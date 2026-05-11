<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Generated\Shared\Transfer\ToolInvocationTransfer;
use Generated\Shared\Transfer\UsageTransfer;
use InvalidArgumentException;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface;
use NeuronAI\Chat\Messages\ContentBlocks\FileContent;
use NeuronAI\Chat\Messages\ContentBlocks\ImageContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use ReflectionClass;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor\MessageContentExtractorInterface;

class NeuronAiMessageMapper implements NeuronAiMessageMapperInterface
{
    public function __construct(
        protected TransferJsonSchemaMapperInterface $transferJsonSchemaMapper,
        protected MessageContentExtractorInterface $messageContentExtractor,
    ) {
    }

    public function mapPromptMessageToProviderMessage(PromptMessageTransfer $promptMessageTransfer): Message
    {
        $contentBlocks = $this->buildContentBlocksFromPromptMessage($promptMessageTransfer);

        return new UserMessage($contentBlocks);
    }

    public function mapProviderResponseToPromptResponse(Message $message): PromptResponseTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent($this->getNormalizedContent($message))
            ->setReasoning($this->messageContentExtractor->extractReasoning($message))
            ->setUsage($this->mapUsageToUsageTransfer($message->getUsage()));

        foreach ($this->extractAttachmentBlocks($message) as $contentBlock) {
            $promptMessageTransfer->addAttachment($this->mapContentBlockToAttachmentTransfer($contentBlock));
        }

        return (new PromptResponseTransfer())
            ->setMessage($promptMessageTransfer);
    }

    public function mapProviderMessageToPromptMessage(Message $message): PromptMessageTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setType($this->mapMessageRole($message))
            ->setContent($this->getNormalizedContent($message))
            ->setReasoning($this->messageContentExtractor->extractReasoning($message))
            ->setUsage($this->mapUsageToUsageTransfer($message->getUsage()));

        foreach ($this->extractAttachmentBlocks($message) as $contentBlock) {
            $promptMessageTransfer->addAttachment($this->mapContentBlockToAttachmentTransfer($contentBlock));
        }

        if ($message instanceof ToolCallMessage || $message instanceof ToolResultMessage) {
            $this->mapToolsToPromptMessage($message, $promptMessageTransfer);
        }

        return $promptMessageTransfer;
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     *
     * @return array<\Generated\Shared\Transfer\PromptMessageTransfer>
     */
    public function mapProviderMessagesToPromptMessages(array $messages): array
    {
        $promptMessages = [];

        foreach ($messages as $message) {
            $promptMessages[] = $this->mapProviderMessageToPromptMessage($message);
        }

        return $promptMessages;
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $structuredResponseTransfer
     *
     * @return array<string, mixed>
     */
    public function mapTransferToStructuredResponseFormat(AbstractTransfer $structuredResponseTransfer): array
    {
        return $this->transferJsonSchemaMapper->buildJsonSchema($structuredResponseTransfer);
    }

    public function mapProviderStructuredResponseToTransfer(Message $message, AbstractTransfer $structuredResponseTransfer): AbstractTransfer
    {
        $messageContent = $this->messageContentExtractor->extractFinalText($message) ?? '';
        $content = $this->transferJsonSchemaMapper->extractJsonFromText($messageContent);

        $structuredResponseTransfer->fromArray($content, true);

        $this->assertTransferPropertiesAreFilled($structuredResponseTransfer, $messageContent);

        return $structuredResponseTransfer;
    }

    protected function getNormalizedContent(Message $message): ?string
    {
        $content = $this->messageContentExtractor->extractFinalText($message);

        if ($content === null) {
            return null;
        }

        $extracted = $this->transferJsonSchemaMapper->extractJsonFromText($content);

        if ($extracted === []) {
            return $content;
        }

        $encoded = json_encode($extracted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? $content : $encoded;
    }

    protected function mapUsageToUsageTransfer(?Usage $usage): ?UsageTransfer
    {
        if ($usage === null) {
            return null;
        }

        return (new UsageTransfer())
            ->setInputTokens($usage->inputTokens)
            ->setOutputTokens($usage->outputTokens);
    }

    protected function mapMessageRole(Message $message): string
    {
        // Specific subtypes must be checked before their parent classes:
        // ToolCallMessage extends AssistantMessage, ToolResultMessage extends UserMessage
        return match (true) {
            $message instanceof ToolCallMessage => AiFoundationConstants::MESSAGE_TYPE_TOOL_CALL,
            $message instanceof ToolResultMessage => AiFoundationConstants::MESSAGE_TYPE_TOOL_RESULT,
            $message instanceof UserMessage => AiFoundationConstants::MESSAGE_TYPE_USER,
            $message instanceof AssistantMessage => AiFoundationConstants::MESSAGE_TYPE_ASSISTANT,
            default => AiFoundationConstants::MESSAGE_TYPE_ASSISTANT,
        };
    }

    protected function mapToolsToPromptMessage(
        ToolCallMessage|ToolResultMessage $message,
        PromptMessageTransfer $promptMessageTransfer,
    ): void {
        foreach ($message->getTools() as $tool) {
            $toolInvocationTransfer = (new ToolInvocationTransfer())
                ->setName($tool->getName())
                ->setArguments($tool->getInputs());

            if ($message instanceof ToolResultMessage) {
                $toolInvocationTransfer->setResult($tool->getResult());
            }

            $promptMessageTransfer->addToolInvocation($toolInvocationTransfer);
        }
    }

    /**
     * @return array<\NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface>
     */
    protected function buildContentBlocksFromPromptMessage(PromptMessageTransfer $promptMessageTransfer): array
    {
        $contentBlocks = [];

        $textContent = $promptMessageTransfer->getContent() ?? $promptMessageTransfer->getContentData();

        if (is_string($textContent) && $textContent !== '') {
            $contentBlocks[] = new TextContent($textContent);
        }

        foreach ($promptMessageTransfer->getAttachments() as $attachmentTransfer) {
            $contentBlocks[] = $this->mapAttachmentTransferToContentBlock($attachmentTransfer);
        }

        return $contentBlocks;
    }

    /**
     * @return array<\NeuronAI\Chat\Messages\ContentBlocks\ImageContent|\NeuronAI\Chat\Messages\ContentBlocks\FileContent>
     */
    protected function extractAttachmentBlocks(Message $message): array
    {
        $attachmentBlocks = [];

        foreach ($message->getContentBlocks() as $contentBlock) {
            if ($contentBlock instanceof ImageContent || $contentBlock instanceof FileContent) {
                $attachmentBlocks[] = $contentBlock;
            }
        }

        return $attachmentBlocks;
    }

    protected function mapAttachmentTransferToContentBlock(AttachmentTransfer $attachmentTransfer): ContentBlockInterface
    {
        $type = $attachmentTransfer->getTypeOrFail();
        $sourceType = $this->resolveSourceTypeFromAttachmentContentType($attachmentTransfer->getContentTypeOrFail());
        $content = $attachmentTransfer->getContentOrFail();
        $mediaType = $attachmentTransfer->getMediaType();

        if ($type === AiFoundationConstants::ATTACHMENT_TYPE_IMAGE) {
            return new ImageContent($content, $sourceType, $mediaType);
        }

        return new FileContent($content, $sourceType, $mediaType, $attachmentTransfer->getFilename());
    }

    protected function mapContentBlockToAttachmentTransfer(ImageContent|FileContent $contentBlock): AttachmentTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())
            ->setContent($contentBlock->content)
            ->setContentType($this->resolveAttachmentContentTypeFromSourceType($contentBlock->sourceType))
            ->setMediaType($contentBlock->mediaType);

        if ($contentBlock instanceof ImageContent) {
            return $attachmentTransfer->setType(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE);
        }

        return $attachmentTransfer
            ->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)
            ->setFilename($contentBlock->filename);
    }

    protected function resolveSourceTypeFromAttachmentContentType(string $contentType): SourceType
    {
        return match ($contentType) {
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64 => SourceType::BASE64,
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL => SourceType::URL,
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_ID => SourceType::ID,
            default => throw new InvalidArgumentException(
                sprintf('Unsupported attachment content type "%s".', $contentType),
            ),
        };
    }

    protected function resolveAttachmentContentTypeFromSourceType(SourceType $sourceType): string
    {
        return match ($sourceType) {
            SourceType::BASE64 => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64,
            SourceType::URL => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL,
            SourceType::ID => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_ID,
        };
    }

    protected function assertTransferPropertiesAreFilled(AbstractTransfer $transfer, string $responseContent): void
    {
        $reflectionClass = new ReflectionClass($transfer);
        $transferMetadataProperty = $reflectionClass->getProperty('transferMetadata');
        $transferMetadataProperty->setAccessible(true);
        $metadata = $transferMetadataProperty->getValue($transfer);

        $missingProperties = [];

        foreach ($metadata as $propertyMetadata) {
            $propertyName = $propertyMetadata['name_underscore'];
            $getterMethod = 'get' . str_replace('_', '', ucwords($propertyMetadata['name_underscore'], '_'));

            if (!method_exists($transfer, $getterMethod)) {
                continue;
            }

            $propertyValue = $transfer->$getterMethod();

            if ($propertyValue === null) {
                $missingProperties[] = $propertyName;
            }

            if ($propertyMetadata['is_collection'] && $propertyValue instanceof ArrayObject && $propertyValue->count() === 0) {
                $missingProperties[] = sprintf('%s (empty collection)', $propertyName);
            }
        }

        if ($missingProperties !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Failed to map structured response to transfer. Missing or empty properties: %s. Response content: %s',
                    implode(', ', $missingProperties),
                    $responseContent,
                ),
            );
        }
    }
}
