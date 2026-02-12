<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationRepository extends AbstractRepository implements AiFoundationRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer {
        $conversationHistoryCollectionTransfer = new ConversationHistoryCollectionTransfer();

        $conversationHistoryQuery = $this->getFactory()->createConversationHistoryQuery();
        $conversationHistoryQuery = $this->applyConversationHistoryFilters(
            $conversationHistoryQuery,
            $conversationHistoryCriteriaTransfer,
        );

        $conversationHistoryEntities = $conversationHistoryQuery->find()->getArrayCopy();

        return $this->getFactory()
            ->createConversationHistoryMapper()
            ->mapConversationHistoryEntityCollectionToConversationHistoryCollectionTransfer(
                $conversationHistoryEntities,
                $conversationHistoryCollectionTransfer,
            );
    }

    /**
     * @param \Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery $query
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery
     */
    protected function applyConversationHistoryFilters(
        SpyAiConversationHistoryQuery $query,
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): SpyAiConversationHistoryQuery {
        $conditions = $conversationHistoryCriteriaTransfer->getConversationHistoryConditions();

        if ($conditions === null) {
            return $query;
        }

        $conversationReferences = $conditions->getConversationReferences();

        if ($conversationReferences !== []) {
            $query->filterByConversationReference_In($conversationReferences);
        }

        return $query;
    }
}
