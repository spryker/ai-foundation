<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\ChatSession;

use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;

class ConversationHistoryDeleter implements ConversationHistoryDeleterInterface
{
    protected const string ERROR_MESSAGE_CONVERSATION_REFERENCES_MISSING = 'At least one conversation reference is required to delete chat history.';

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager
    ) {
    }

    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        $conversationHistoryCollectionResponseTransfer = (new ConversationHistoryCollectionResponseTransfer())->setIsSuccessful(false);

        $conversationReferences = $this->resolveConversationReferences($conversationHistoryCollectionDeleteCriteriaTransfer);

        if ($conversationReferences === []) {
            return $conversationHistoryCollectionResponseTransfer->addError(
                (new ErrorTransfer())->setMessage(static::ERROR_MESSAGE_CONVERSATION_REFERENCES_MISSING),
            );
        }

        $this->entityManager->deleteConversationHistory(
            (new ConversationHistoryCriteriaTransfer())->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())->setConversationReferences($conversationReferences),
            ),
        );

        return $conversationHistoryCollectionResponseTransfer->setIsSuccessful(true);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveConversationReferences(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): array {
        $conversationReferences = array_filter(
            $conversationHistoryCollectionDeleteCriteriaTransfer->getConversationHistoryConditions()?->getConversationReferences() ?? [],
            static fn (string $conversationReference): bool => trim($conversationReference) !== '',
        );

        return array_values(array_unique($conversationReferences));
    }
}
