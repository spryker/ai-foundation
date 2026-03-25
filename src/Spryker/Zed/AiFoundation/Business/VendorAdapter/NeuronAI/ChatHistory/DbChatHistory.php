<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistory;

use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\History\ChatHistoryInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class DbChatHistory extends AbstractChatHistory
{
    protected const int DEFAULT_MAX_ATTACHMENT_STORAGE_SIZE_IN_BYTES = 0;

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager,
        protected AiFoundationRepositoryInterface $repository,
        protected NeuronAiMessageMapper $messageMapper,
        protected string $conversationReference,
        int $contextWindow = 50000,
        protected int $maxAttachmentStorageSizeInBytes = self::DEFAULT_MAX_ATTACHMENT_STORAGE_SIZE_IN_BYTES,
    ) {
        parent::__construct($contextWindow);

        $this->loadHistoryFromDatabase();
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     *
     * @return \NeuronAI\Chat\History\ChatHistoryInterface
     */
    public function setMessages(array $messages): ChatHistoryInterface
    {
        $messagesArray = array_map(fn ($message): array => $message->jsonSerialize(), $this->getMessages());
        $originalMessages = json_encode($this->sanitizeMessagesForStorage($messagesArray));

        $conversationHistoryTransfer = (new ConversationHistoryTransfer())
            ->setConversationReference($this->conversationReference)
            ->setOriginalMessages($originalMessages !== false ? $originalMessages : '[]');

        $this->entityManager->saveConversationHistory($conversationHistoryTransfer);

        return $this;
    }

    /**
     * @param array<array<string, mixed>> $messages
     *
     * @return array<array<string, mixed>>
     */
    protected function sanitizeMessagesForStorage(array $messages): array
    {
        return array_map(function (array $message): array {
            if (empty($message['attachments'])) {
                return $message;
            }

            $message['attachments'] = array_values(
                array_filter(
                    $message['attachments'],
                    fn (array $attachment): bool => $this->isAttachmentStorable($attachment),
                ),
            );

            if ($message['attachments'] === []) {
                unset($message['attachments']);
            }

            return $message;
        }, $messages);
    }

    /**
     * @param array<string, mixed> $attachment
     */
    protected function isAttachmentStorable(array $attachment): bool
    {
        if (($attachment['content_type'] ?? '') !== AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64) {
            return true;
        }

        if ($this->maxAttachmentStorageSizeInBytes === 0) {
            return false;
        }

        return strlen((string)($attachment['content'] ?? '')) <= $this->maxAttachmentStorageSizeInBytes;
    }

    protected function clear(): ChatHistoryInterface
    {
        $criteria = (new ConversationHistoryCriteriaTransfer())
            ->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())
                    ->setConversationReferences([$this->conversationReference]),
            );

        $this->entityManager->deleteConversationHistory($criteria);

        return $this;
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
