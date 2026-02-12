<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationEntityManager extends AbstractEntityManager implements AiFoundationEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\ConversationHistoryTransfer $conversationHistoryTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryTransfer
     */
    public function saveConversationHistory(
        ConversationHistoryTransfer $conversationHistoryTransfer
    ): ConversationHistoryTransfer {
        $entity = $this->getFactory()
            ->createConversationHistoryQuery()
            ->filterByConversationReference($conversationHistoryTransfer->getConversationReferenceOrFail())
            ->findOneOrCreate();

        $entity = $this->getFactory()
            ->createConversationHistoryMapper()
            ->mapTransferToEntity($conversationHistoryTransfer, $entity);

        $entity->save();

        return $this->getFactory()
            ->createConversationHistoryMapper()
            ->mapEntityToTransfer($entity, $conversationHistoryTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return void
     */
    public function deleteConversationHistory(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): void {
        $conversationHistoryQuery = $this->getFactory()->createConversationHistoryQuery();

        $conversationHistoryQuery = $this->applyConversationHistoryFilters(
            $conversationHistoryQuery,
            $conversationHistoryCriteriaTransfer,
        );

        $conversationHistoryQuery->delete();
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
