<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationRepository extends AbstractRepository implements AiFoundationRepositoryInterface
{
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

    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        $aiWorkflowItemCollection = new AiWorkflowItemCollectionTransfer();
        $aiWorkflowItemQuery = SpyAiWorkflowItemQuery::create();

        $aiWorkflowItemQuery = $this->applyAiWorkflowItemFilters(
            $aiWorkflowItemQuery,
            $aiWorkflowItemCriteriaTransfer,
        );

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem> $aiWorkflowItemEntities */
        $aiWorkflowItemEntities = $aiWorkflowItemQuery->find();

        if ($aiWorkflowItemEntities->count() === 0) {
            return $aiWorkflowItemCollection;
        }

        return $this->getFactory()
            ->createAiWorkflowItemMapper()
            ->mapAiWorkflowItemEntityCollectionToAiWorkflowItemCollectionTransfer($aiWorkflowItemEntities, $aiWorkflowItemCollection);
    }

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

    protected function applyAiWorkflowItemFilters(
        SpyAiWorkflowItemQuery $aiWorkflowItemQuery,
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): SpyAiWorkflowItemQuery {
        $aiWorkflowItemConditions = $aiWorkflowItemCriteriaTransfer->getAiWorkflowItemConditions();

        if ($aiWorkflowItemConditions === null) {
            return $aiWorkflowItemQuery;
        }

        if ($aiWorkflowItemConditions->getAiWorkflowItemIds()) {
            $aiWorkflowItemQuery->filterByIdAiWorkflowItem_In(
                $aiWorkflowItemConditions->getAiWorkflowItemIds(),
            );
        }

        if ($aiWorkflowItemConditions->getStateIds()) {
            $aiWorkflowItemQuery->filterByFkStateMachineItemState_In(
                $aiWorkflowItemConditions->getStateIds(),
            );
        }

        return $aiWorkflowItemQuery;
    }
}
