<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistory;

use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use NeuronAI\Chat\Enums\ContentBlockType;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\History\AbstractChatHistory;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapperInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class DbChatHistory extends AbstractChatHistory
{
    protected const int DEFAULT_MAX_ATTACHMENT_STORAGE_SIZE_IN_BYTES = 0;

    /**
     * @var array<\NeuronAI\Chat\Enums\ContentBlockType>
     */
    protected const array BINARY_CONTENT_BLOCK_TYPES = [
        ContentBlockType::IMAGE,
        ContentBlockType::FILE,
        ContentBlockType::AUDIO,
        ContentBlockType::VIDEO,
    ];

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager,
        protected AiFoundationRepositoryInterface $repository,
        protected NeuronAiMessageMapperInterface $messageMapper,
        protected string $conversationReference,
        int $contextWindow = 50000,
        protected int $maxAttachmentStorageSizeInBytes = self::DEFAULT_MAX_ATTACHMENT_STORAGE_SIZE_IN_BYTES,
    ) {
        parent::__construct($contextWindow);

        $this->loadHistoryFromDatabase();
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     */
    protected function setMessages(array $messages): void
    {
        $messagesArray = array_map(fn ($message): array => $message->jsonSerialize(), $messages);
        $originalMessages = json_encode($this->sanitizeMessagesForStorage($messagesArray));

        $conversationHistoryTransfer = (new ConversationHistoryTransfer())
            ->setConversationReference($this->conversationReference)
            ->setOriginalMessages($originalMessages !== false ? $originalMessages : '[]');

        $this->entityManager->saveConversationHistory($conversationHistoryTransfer);
    }

    /**
     * @param array<array<string, mixed>> $messages
     *
     * @return array<array<string, mixed>>
     */
    protected function sanitizeMessagesForStorage(array $messages): array
    {
        return array_map(function (array $message): array {
            if (empty($message['content']) || !is_array($message['content'])) {
                return $message;
            }

            $message['content'] = array_values(
                array_filter(
                    $message['content'],
                    fn ($contentBlock): bool => !is_array($contentBlock) || $this->isContentBlockStorable($contentBlock),
                ),
            );

            return $message;
        }, $messages);
    }

    /**
     * @param array<string, mixed> $contentBlock
     */
    protected function isContentBlockStorable(array $contentBlock): bool
    {
        if (!in_array($contentBlock['type'] ?? null, static::BINARY_CONTENT_BLOCK_TYPES, true)) {
            return true;
        }

        if (($contentBlock['source_type'] ?? null) !== SourceType::BASE64) {
            return true;
        }

        if ($this->maxAttachmentStorageSizeInBytes === 0) {
            return false;
        }

        return strlen((string)($contentBlock['content'] ?? '')) <= $this->maxAttachmentStorageSizeInBytes;
    }

    protected function clear(): void
    {
        $criteria = (new ConversationHistoryCriteriaTransfer())
            ->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())
                    ->setConversationReferences([$this->conversationReference]),
            );

        $this->entityManager->deleteConversationHistory($criteria);
    }

    protected function loadHistoryFromDatabase(): void
    {
        $criteria = (new ConversationHistoryCriteriaTransfer())
            ->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())
                    ->setConversationReferences([$this->conversationReference]),
            );

        $collection = $this->repository->getConversationHistoryCollection($criteria);

        if ($collection->getConversationHistories()->count() === 0) {
            return;
        }

        $conversationHistoryTransfer = $collection->getConversationHistories()->offsetGet(0);

        $messagesJson = $conversationHistoryTransfer->getOriginalMessages();

        if ($messagesJson === null) {
            return;
        }

        $messages = json_decode($messagesJson, true) ?? [];

        $this->history = $this->deserializeMessages($messages);
    }
}
