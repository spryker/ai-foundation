<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor;

use NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;

class MessageContentExtractor implements MessageContentExtractorInterface
{
    protected const string TEXT_SEGMENT_SEPARATOR = ' ';

    protected const string REASONING_SEGMENT_SEPARATOR = "\n\n";

    public function extractFinalText(Message $message): ?string
    {
        $segments = [];

        foreach ($message->getContentBlocks() as $contentBlock) {
            if (!$this->isPlainTextContent($contentBlock)) {
                continue;
            }

            $segments[] = $contentBlock->getContent();
        }

        if ($segments === []) {
            return null;
        }

        return implode(static::TEXT_SEGMENT_SEPARATOR, $segments);
    }

    public function extractReasoning(Message $message): ?string
    {
        $segments = [];

        foreach ($message->getContentBlocks() as $contentBlock) {
            if (!$contentBlock instanceof ReasoningContent) {
                continue;
            }

            $segments[] = $contentBlock->getContent();
        }

        if ($segments === []) {
            return null;
        }

        return implode(static::REASONING_SEGMENT_SEPARATOR, $segments);
    }

    protected function isPlainTextContent(ContentBlockInterface $contentBlock): bool
    {
        return $contentBlock instanceof TextContent && !$contentBlock instanceof ReasoningContent;
    }
}
