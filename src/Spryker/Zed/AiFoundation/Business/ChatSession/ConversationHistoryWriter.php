<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\ChatSession;

use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class ConversationHistoryWriter implements ConversationHistoryWriterInterface
{
    use TransactionTrait;

    protected const string ERROR_MESSAGE_CONVERSATION_HISTORIES_MISSING = 'At least one conversation history is required to save chat history.';

    protected const string ERROR_MESSAGE_CONVERSATION_REFERENCE_MISSING = 'A conversation reference is required to save chat history.';

    protected const string ORIGINAL_MESSAGES_EMPTY = '[]';

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager
    ) {
    }

    public function saveConversationHistoryCollection(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        $conversationHistoryCollectionResponseTransfer = (new ConversationHistoryCollectionResponseTransfer())->setIsSuccessful(false);

        if ($conversationHistoryCollectionRequestTransfer->getConversationHistories()->count() === 0) {
            return $conversationHistoryCollectionResponseTransfer->addError(
                (new ErrorTransfer())->setMessage(static::ERROR_MESSAGE_CONVERSATION_HISTORIES_MISSING),
            );
        }

        if (!$this->hasConversationReferences($conversationHistoryCollectionRequestTransfer)) {
            return $conversationHistoryCollectionResponseTransfer->addError(
                (new ErrorTransfer())->setMessage(static::ERROR_MESSAGE_CONVERSATION_REFERENCE_MISSING),
            );
        }

        return $this->getTransactionHandler()->handleTransaction(
            fn (): ConversationHistoryCollectionResponseTransfer => $this->executeSaveConversationHistoryCollectionTransaction(
                $conversationHistoryCollectionRequestTransfer,
                $conversationHistoryCollectionResponseTransfer,
            ),
        );
    }

    protected function hasConversationReferences(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): bool {
        foreach ($conversationHistoryCollectionRequestTransfer->getConversationHistories() as $conversationHistoryTransfer) {
            if ((string)$conversationHistoryTransfer->getConversationReference() === '') {
                return false;
            }
        }

        return true;
    }

    protected function executeSaveConversationHistoryCollectionTransaction(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer,
        ConversationHistoryCollectionResponseTransfer $conversationHistoryCollectionResponseTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        foreach ($conversationHistoryCollectionRequestTransfer->getConversationHistories() as $conversationHistoryTransfer) {
            $conversationHistoryCollectionResponseTransfer->addConversationHistory(
                $this->entityManager->saveConversationHistory(
                    (new ConversationHistoryTransfer())
                        ->setConversationReference($conversationHistoryTransfer->getConversationReference())
                        ->setOriginalMessages($conversationHistoryTransfer->getOriginalMessages() ?? static::ORIGINAL_MESSAGES_EMPTY),
                ),
            );
        }

        return $conversationHistoryCollectionResponseTransfer->setIsSuccessful(true);
    }
}
