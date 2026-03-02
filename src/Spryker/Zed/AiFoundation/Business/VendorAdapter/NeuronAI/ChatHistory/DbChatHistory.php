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
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class DbChatHistory extends AbstractChatHistory
{
    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager,
        protected AiFoundationRepositoryInterface $repository,
        protected NeuronAiMessageMapper $messageMapper,
        protected string $conversationReference,
        int $contextWindow = 50000,
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
        $originalMessages = json_encode($this->jsonSerialize());

        $conversationHistoryTransfer = (new ConversationHistoryTransfer())
            ->setConversationReference($this->conversationReference)
            ->setOriginalMessages($originalMessages !== false ? $originalMessages : '[]');

        $this->entityManager->saveConversationHistory($conversationHistoryTransfer);

        return $this;
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
