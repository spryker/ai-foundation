<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\PromptStreamChunkTransfer;
use NeuronAI\Chat\Messages\Stream\Chunks\ReasoningChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\StreamChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use Spryker\Shared\AiFoundation\Stream\PromptStreamChunkType;

class StreamChunkMapper implements StreamChunkMapperInterface
{
    public function mapStreamChunkToPromptStreamChunk(StreamChunk $streamChunk): ?PromptStreamChunkTransfer
    {
        if ($streamChunk instanceof TextChunk) {
            return $this->createContentChunk(PromptStreamChunkType::Text, $streamChunk->content);
        }

        if ($streamChunk instanceof ReasoningChunk) {
            return $this->createContentChunk(PromptStreamChunkType::Reasoning, $streamChunk->content);
        }

        return null;
    }

    protected function createContentChunk(PromptStreamChunkType $promptStreamChunkType, string $content): PromptStreamChunkTransfer
    {
        return (new PromptStreamChunkTransfer())
            ->setType($promptStreamChunkType->value)
            ->setContent($content);
    }
}
