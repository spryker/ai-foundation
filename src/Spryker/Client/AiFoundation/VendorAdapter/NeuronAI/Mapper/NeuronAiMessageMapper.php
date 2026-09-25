<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\UsageTransfer;
use InvalidArgumentException;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface;
use NeuronAI\Chat\Messages\ContentBlocks\FileContent;
use NeuronAI\Chat\Messages\ContentBlocks\ImageContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use Spryker\Shared\AiFoundation\AiFoundationConstants;

class NeuronAiMessageMapper implements NeuronAiMessageMapperInterface
{
    public function mapPromptMessageToUserMessage(PromptMessageTransfer $promptMessageTransfer): UserMessage
    {
        $attachmentTransfers = $promptMessageTransfer->getAttachments();

        if (count($attachmentTransfers) === 0) {
            return new UserMessage((string)$promptMessageTransfer->getContent());
        }

        return new UserMessage($this->buildContentBlocks($promptMessageTransfer));
    }

    public function mapUsageToUsageTransfer(?Usage $usage): ?UsageTransfer
    {
        if ($usage === null) {
            return null;
        }

        return (new UsageTransfer())
            ->setInputTokens($usage->inputTokens)
            ->setOutputTokens($usage->outputTokens);
    }

    /**
     * @return array<\NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface>
     */
    protected function buildContentBlocks(PromptMessageTransfer $promptMessageTransfer): array
    {
        $contentBlocks = [];

        $content = $promptMessageTransfer->getContent();

        if (is_string($content) && $content !== '') {
            $contentBlocks[] = new TextContent($content);
        }

        foreach ($promptMessageTransfer->getAttachments() as $attachmentTransfer) {
            $contentBlocks[] = $this->mapAttachmentTransferToContentBlock($attachmentTransfer);
        }

        return $contentBlocks;
    }

    protected function mapAttachmentTransferToContentBlock(AttachmentTransfer $attachmentTransfer): ContentBlockInterface
    {
        $sourceType = $this->resolveSourceTypeFromAttachmentContentType($attachmentTransfer->getContentTypeOrFail());
        $content = $attachmentTransfer->getContentOrFail();
        $mediaType = $attachmentTransfer->getMediaType();

        if ($attachmentTransfer->getTypeOrFail() === AiFoundationConstants::ATTACHMENT_TYPE_IMAGE) {
            return new ImageContent($content, $sourceType, $mediaType);
        }

        return new FileContent($content, $sourceType, $mediaType, $attachmentTransfer->getFilename());
    }

    /**
     * @throws \InvalidArgumentException
     */
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
}
