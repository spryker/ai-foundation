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
use NeuronAI\Chat\Attachments\Attachment;
use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use ReflectionClass;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapperInterface;

class NeuronAiMessageMapper
{
    public function __construct(
        protected TransferJsonSchemaMapperInterface $transferJsonSchemaMapper,
    ) {
    }

    public function mapPromptMessageToProviderMessage(PromptMessageTransfer $promptMessageTransfer): Message
    {
        $content = $promptMessageTransfer->getContent() ?? $promptMessageTransfer->getContentData();

        $userMessage = new UserMessage($content);

        foreach ($promptMessageTransfer->getAttachments() as $attachmentTransfer) {
            $attachment = $this->mapAttachmentTransferToAttachment($attachmentTransfer);
            $userMessage->addAttachment($attachment);
        }

        return $userMessage;
    }

    public function mapProviderResponseToPromptResponse(Message $message): PromptResponseTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent($message->getContent())
            ->setUsage($this->mapUsageToUsageTransfer($message->getUsage()));

        foreach ($message->getAttachments() as $attachment) {
            $attachmentTransfer = $this->mapAttachmentToAttachmentTransfer($attachment);
            $promptMessageTransfer->addAttachment($attachmentTransfer);
        }

        return (new PromptResponseTransfer())
            ->setMessage($promptMessageTransfer);
    }

    public function mapProviderMessageToPromptMessage(Message $message): PromptMessageTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setType($this->mapMessageRole($message))
            ->setContent($message->getContent())
            ->setUsage($this->mapUsageToUsageTransfer($message->getUsage()));

        foreach ($message->getAttachments() as $attachment) {
            $attachmentTransfer = $this->mapAttachmentToAttachmentTransfer($attachment);
            $promptMessageTransfer->addAttachment($attachmentTransfer);
        }

        if ($message instanceof ToolCallMessage || $message instanceof ToolCallResultMessage) {
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
        // ToolCallMessage extends AssistantMessage, ToolCallResultMessage extends UserMessage
        return match (true) {
            $message instanceof ToolCallMessage => AiFoundationConstants::MESSAGE_TYPE_TOOL_CALL,
            $message instanceof ToolCallResultMessage => AiFoundationConstants::MESSAGE_TYPE_TOOL_RESULT,
            $message instanceof UserMessage => AiFoundationConstants::MESSAGE_TYPE_USER,
            $message instanceof AssistantMessage => AiFoundationConstants::MESSAGE_TYPE_ASSISTANT,
            default => AiFoundationConstants::MESSAGE_TYPE_ASSISTANT,
        };
    }

    protected function mapToolsToPromptMessage(
        ToolCallMessage|ToolCallResultMessage $message,
        PromptMessageTransfer $promptMessageTransfer,
    ): void {
        foreach ($message->getTools() as $tool) {
            $toolInvocationTransfer = (new ToolInvocationTransfer())
                ->setName($tool->getName())
                ->setArguments($tool->getInputs());

            if ($message instanceof ToolCallResultMessage) {
                $toolInvocationTransfer->setResult($tool->getResult());
            }

            $promptMessageTransfer->addToolInvocation($toolInvocationTransfer);
        }
    }

    protected function mapAttachmentTransferToAttachment(AttachmentTransfer $attachmentTransfer): Attachment
    {
        $type = $this->mapAttachmentType($attachmentTransfer->getTypeOrFail());
        $contentType = $this->mapAttachmentContentType($attachmentTransfer->getContentTypeOrFail());

        return new Attachment(
            type: $type,
            content: $attachmentTransfer->getContentOrFail(),
            contentType: $contentType,
            mediaType: $attachmentTransfer->getMediaType(),
        );
    }

    protected function mapAttachmentToAttachmentTransfer(Attachment $attachment): AttachmentTransfer
    {
        $type = $this->mapAttachmentTypeToConstant($attachment->type);
        $contentType = $this->mapAttachmentContentTypeToConstant($attachment->contentType);

        return (new AttachmentTransfer())
            ->setType($type)
            ->setContent($attachment->content)
            ->setContentType($contentType)
            ->setMediaType($attachment->mediaType);
    }

    protected function mapAttachmentType(string $type): AttachmentType
    {
        return match ($type) {
            AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT => AttachmentType::DOCUMENT,
            AiFoundationConstants::ATTACHMENT_TYPE_IMAGE => AttachmentType::IMAGE,
            default => AttachmentType::DOCUMENT,
        };
    }

    protected function mapAttachmentContentType(string $contentType): AttachmentContentType
    {
        return match ($contentType) {
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL => AttachmentContentType::URL,
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64 => AttachmentContentType::BASE64,
            default => AttachmentContentType::URL,
        };
    }

    protected function mapAttachmentTypeToConstant(AttachmentType $type): string
    {
        return match ($type) {
            AttachmentType::DOCUMENT => AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT,
            AttachmentType::IMAGE => AiFoundationConstants::ATTACHMENT_TYPE_IMAGE,
        };
    }

    protected function mapAttachmentContentTypeToConstant(AttachmentContentType $contentType): string
    {
        return match ($contentType) {
            AttachmentContentType::URL => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL,
            AttachmentContentType::BASE64 => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64,
            default => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL,
        };
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
        $content = $this->transferJsonSchemaMapper->extractJsonFromText($message->getContent());

        $structuredResponseTransfer->fromArray($content, true);

        $this->assertTransferPropertiesAreFilled($structuredResponseTransfer, $message->getContent());

        return $structuredResponseTransfer;
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
