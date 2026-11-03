<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use NeuronAI\Chat\Enums\ContentBlockType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;

class ChatHistoryReasoningFilter implements ChatHistoryReasoningFilterInterface
{
    protected const string MESSAGE_METADATA_KEY_PROVIDER_NAME = 'ai_provider_name';

    protected const string MESSAGE_METADATA_KEY_MODEL_NAME = 'ai_model_name';

    protected const string MESSAGE_KEY_CONTENT = 'content';

    protected const string CONTENT_BLOCK_KEY_TYPE = 'type';

    protected const string CONTENT_BLOCK_KEY_META = 'meta';

    /**
     * @var array<string, string|null>
     */
    protected const array REASONING_METADATA_KEYS_TO_TOOL_POSITIONS_METADATA_KEYS = [
        'aws_redacted_reasoning' => 'aws_tool_positions',
        'anthropic_redacted_thinking' => 'anthropic_tool_positions',
        'thought_signature' => null,
        'reasoning_content' => null,
    ];

    public function markMessageOrigin(Message $message, string $providerName, ?string $modelName): Message
    {
        if (!$message instanceof AssistantMessage) {
            return $message;
        }

        return $message
            ->addMetadata(static::MESSAGE_METADATA_KEY_PROVIDER_NAME, $providerName)
            ->addMetadata(static::MESSAGE_METADATA_KEY_MODEL_NAME, $modelName);
    }

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     *
     * @return array<int, array<string, mixed>>
     */
    public function removeForeignReasoning(array $serializedMessages, string $providerName, ?string $modelName): array
    {
        foreach ($serializedMessages as $index => $serializedMessage) {
            if ($this->isMessageOrigin($serializedMessage, $providerName, $modelName)) {
                continue;
            }

            $serializedMessages[$index] = $this->removeReasoning($serializedMessage);
        }

        return $serializedMessages;
    }

    /**
     * @param array<string, mixed> $serializedMessage
     */
    protected function isMessageOrigin(array $serializedMessage, string $providerName, ?string $modelName): bool
    {
        return ($serializedMessage[static::MESSAGE_METADATA_KEY_PROVIDER_NAME] ?? null) === $providerName
            && ($serializedMessage[static::MESSAGE_METADATA_KEY_MODEL_NAME] ?? null) === $modelName;
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return array<string, mixed>
     */
    protected function removeReasoning(array $serializedMessage): array
    {
        $serializedMessage = $this->recalculateToolPositions($serializedMessage);
        $serializedMessage = $this->removeReasoningContentBlocks($serializedMessage);

        return $this->removeReasoningMetadata($serializedMessage);
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return array<string, mixed>
     */
    protected function recalculateToolPositions(array $serializedMessage): array
    {
        $contentBlocks = $this->getContentBlocks($serializedMessage);

        foreach (static::REASONING_METADATA_KEYS_TO_TOOL_POSITIONS_METADATA_KEYS as $reasoningMetadataKey => $toolPositionsMetadataKey) {
            if ($toolPositionsMetadataKey === null || !is_array($serializedMessage[$toolPositionsMetadataKey] ?? null)) {
                continue;
            }

            $redactedReasoning = $serializedMessage[$reasoningMetadataKey] ?? [];

            $serializedMessage[$toolPositionsMetadataKey] = $this->calculateToolPositionsWithoutReasoning(
                $contentBlocks,
                is_array($redactedReasoning) ? $redactedReasoning : [],
                $serializedMessage[$toolPositionsMetadataKey],
            );
        }

        return $serializedMessage;
    }

    /**
     * @param array<int, mixed> $contentBlocks
     * @param array<mixed> $redactedReasoning
     * @param array<mixed> $toolPositions
     *
     * @return array<int, int>
     */
    protected function calculateToolPositionsWithoutReasoning(array $contentBlocks, array $redactedReasoning, array $toolPositions): array
    {
        $entries = array_map(
            fn (mixed $contentBlock): bool => !$this->isReasoningContentBlock($contentBlock),
            $contentBlocks,
        );

        $insertions = [];

        foreach (array_keys($redactedReasoning) as $position) {
            $insertions[(int)$position] = false;
        }

        foreach ($toolPositions as $toolIndex => $position) {
            $insertions[(int)$position] = (int)$toolIndex;
        }

        ksort($insertions);

        foreach ($insertions as $position => $entry) {
            array_splice($entries, $position, 0, [$entry]);
        }

        $recalculatedToolPositions = [];
        $position = 0;

        foreach ($entries as $entry) {
            if ($entry === false) {
                continue;
            }

            if (is_int($entry)) {
                $recalculatedToolPositions[$entry] = $position;
            }

            $position++;
        }

        ksort($recalculatedToolPositions);

        return $recalculatedToolPositions;
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return array<string, mixed>
     */
    protected function removeReasoningContentBlocks(array $serializedMessage): array
    {
        if (!is_array($serializedMessage[static::MESSAGE_KEY_CONTENT] ?? null)) {
            return $serializedMessage;
        }

        $contentBlocks = array_filter(
            $this->getContentBlocks($serializedMessage),
            fn (mixed $contentBlock): bool => !$this->isReasoningContentBlock($contentBlock),
        );

        $serializedMessage[static::MESSAGE_KEY_CONTENT] = array_values(array_map(
            fn (mixed $contentBlock): mixed => $this->removeContentBlockReasoningMetadata($contentBlock),
            $contentBlocks,
        ));

        return $serializedMessage;
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return array<string, mixed>
     */
    protected function removeReasoningMetadata(array $serializedMessage): array
    {
        return array_diff_key($serializedMessage, static::REASONING_METADATA_KEYS_TO_TOOL_POSITIONS_METADATA_KEYS);
    }

    protected function removeContentBlockReasoningMetadata(mixed $contentBlock): mixed
    {
        if (!is_array($contentBlock) || !is_array($contentBlock[static::CONTENT_BLOCK_KEY_META] ?? null)) {
            return $contentBlock;
        }

        $contentBlock[static::CONTENT_BLOCK_KEY_META] = array_diff_key(
            $contentBlock[static::CONTENT_BLOCK_KEY_META],
            static::REASONING_METADATA_KEYS_TO_TOOL_POSITIONS_METADATA_KEYS,
        );

        return $contentBlock;
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return array<int, mixed>
     */
    protected function getContentBlocks(array $serializedMessage): array
    {
        $contentBlocks = $serializedMessage[static::MESSAGE_KEY_CONTENT] ?? null;

        return is_array($contentBlocks) ? array_values($contentBlocks) : [];
    }

    protected function isReasoningContentBlock(mixed $contentBlock): bool
    {
        return is_array($contentBlock)
            && ($contentBlock[static::CONTENT_BLOCK_KEY_TYPE] ?? null) === ContentBlockType::REASONING->value;
    }
}
