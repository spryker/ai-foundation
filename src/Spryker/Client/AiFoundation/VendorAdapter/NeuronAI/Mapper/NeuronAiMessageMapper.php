<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Generated\Shared\Transfer\ToolInvocationTransfer;
use InvalidArgumentException;
use NeuronAI\Chat\Attachments\Attachment;
use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\ToolInterface;
use ReflectionClass;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class NeuronAiMessageMapper
{
    public function __construct(
        protected TransferJsonSchemaMapperInterface $transferJsonSchemaMapper,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\PromptMessageTransfer $promptMessageTransfer
     *
     * @return \NeuronAI\Chat\Messages\Message
     */
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

    /**
     * @param \NeuronAI\Chat\Messages\Message $message
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function mapProviderResponseToPromptResponse(Message $message): PromptResponseTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent($message->getContent());

        foreach ($message->getAttachments() as $attachment) {
            $attachmentTransfer = $this->mapAttachmentToAttachmentTransfer($attachment);
            $promptMessageTransfer->addAttachment($attachmentTransfer);
        }

        return (new PromptResponseTransfer())
            ->setMessage($promptMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AttachmentTransfer $attachmentTransfer
     *
     * @return \NeuronAI\Chat\Attachments\Attachment
     */
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

    /**
     * @param \NeuronAI\Chat\Attachments\Attachment $attachment
     *
     * @return \Generated\Shared\Transfer\AttachmentTransfer
     */
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

    /**
     * @param string $type
     *
     * @return \NeuronAI\Chat\Enums\AttachmentType
     */
    protected function mapAttachmentType(string $type): AttachmentType
    {
        return match ($type) {
            AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT => AttachmentType::DOCUMENT,
            AiFoundationConstants::ATTACHMENT_TYPE_IMAGE => AttachmentType::IMAGE,
            default => AttachmentType::DOCUMENT,
        };
    }

    /**
     * @param string $contentType
     *
     * @return \NeuronAI\Chat\Enums\AttachmentContentType
     */
    protected function mapAttachmentContentType(string $contentType): AttachmentContentType
    {
        return match ($contentType) {
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL => AttachmentContentType::URL,
            AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64 => AttachmentContentType::BASE64,
            default => AttachmentContentType::URL,
        };
    }

    /**
     * @param \NeuronAI\Chat\Enums\AttachmentType $type
     *
     * @return string
     */
    protected function mapAttachmentTypeToConstant(AttachmentType $type): string
    {
        return match ($type) {
            AttachmentType::DOCUMENT => AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT,
            AttachmentType::IMAGE => AiFoundationConstants::ATTACHMENT_TYPE_IMAGE,
        };
    }

    /**
     * @param \NeuronAI\Chat\Enums\AttachmentContentType $contentType
     *
     * @return string
     */
    protected function mapAttachmentContentTypeToConstant(AttachmentContentType $contentType): string
    {
        return match ($contentType) {
            AttachmentContentType::URL => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL,
            AttachmentContentType::BASE64 => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64,
            default => AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL,
        };
    }

    /**
     * @param array<\NeuronAI\Tools\ToolInterface> $executedTools
     *
     * @return array<\Generated\Shared\Transfer\ToolInvocationTransfer>
     */
    public function mapExecutedToolsToToolInvocations(array $executedTools): array
    {
        $toolInvocations = [];

        foreach ($executedTools as $tool) {
            $toolInvocations[] = $this->mapExecutedToolToToolInvocation($tool);
        }

        return $toolInvocations;
    }

    /**
     * @param \NeuronAI\Tools\ToolInterface $tool
     *
     * @return \Generated\Shared\Transfer\ToolInvocationTransfer
     */
    protected function mapExecutedToolToToolInvocation(ToolInterface $tool): ToolInvocationTransfer
    {
        return (new ToolInvocationTransfer())
            ->setName($tool->getName())
            ->setArguments($tool->getInputs())
            ->setResult($tool->getResult());
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

    /**
     * @template T of \Spryker\Shared\Kernel\Transfer\AbstractTransfer
     *
     * @phpstan-return T
     *
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param T $structuredResponseTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\AbstractTransfer
     */
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
