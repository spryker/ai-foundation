<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
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

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemEntity = new SpyAiWorkflowItem();

        $aiWorkflowItemEntity = $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemTransferToEntity(
            $aiWorkflowItemTransfer,
            $aiWorkflowItemEntity,
        );

        $aiWorkflowItemEntity->save();

        return $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemEntityToTransfer(
            $aiWorkflowItemEntity,
            $aiWorkflowItemTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer Returns original transfer if workflow item not found.
     */
    public function updateAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemTransfer->requireIdAiWorkflowItem();

        $aiWorkflowItemEntity = $this->getFactory()
            ->createAiWorkflowItemQuery()
            ->filterByIdAiWorkflowItem($aiWorkflowItemTransfer->getIdAiWorkflowItem())
            ->findOne();

        if ($aiWorkflowItemEntity === null) {
            return $aiWorkflowItemTransfer;
        }

        $aiWorkflowItemEntity = $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemTransferToEntity(
            $aiWorkflowItemTransfer,
            $aiWorkflowItemEntity,
        );

        $aiWorkflowItemEntity->save();

        return $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemEntityToTransfer(
            $aiWorkflowItemEntity,
            $aiWorkflowItemTransfer,
        );
    }
}
