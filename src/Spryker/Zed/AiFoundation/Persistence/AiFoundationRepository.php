<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Orm\Zed\AiFoundation\Persistence\Map\SpyAiInteractionLogTableMap;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiInteractionLogMapper;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationRepository extends AbstractRepository implements AiFoundationRepositoryInterface
{
    protected const string SQL_TOTAL_REQUESTS = 'COUNT(*)';

    protected const string SQL_TOTAL_TOKENS = 'COALESCE(SUM(%s + %s), 0)';

    protected const string SQL_SUCCESS_COUNT = 'SUM(CASE WHEN %s = true THEN 1 ELSE 0 END)';

    protected const string SQL_AVERAGE_INFERENCE_TIME_MS = 'COALESCE(AVG(%s), 0)';

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

    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionTransfer {
        $aiInteractionLogCollectionTransfer = new AiInteractionLogCollectionTransfer();

        if ($aiInteractionLogCriteriaTransfer->getAiInteractionLogConditions() === null) {
            return $aiInteractionLogCollectionTransfer;
        }

        $aiInteractionLogQuery = $this->getFactory()->createAiInteractionLogQuery();
        $aiInteractionLogQuery = $this->applyAiInteractionLogFilters(
            $aiInteractionLogQuery,
            $aiInteractionLogCriteriaTransfer,
        );

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog> $aiInteractionLogEntities */
        $aiInteractionLogEntities = $aiInteractionLogQuery->find();

        return $this->getFactory()
            ->createAiInteractionLogMapper()
            ->mapAiInteractionLogEntityCollectionToCollectionTransfer(
                $aiInteractionLogEntities,
                $aiInteractionLogCollectionTransfer,
            );
    }

    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer {
        $aiInteractionLogQuery = $this->getFactory()->createAiInteractionLogQuery();
        $aiInteractionLogQuery = $this->applyAiInteractionLogFilters(
            $aiInteractionLogQuery,
            $aiInteractionLogCriteriaTransfer,
        );

        $aiInteractionLogQuery
            ->withColumn(static::SQL_TOTAL_REQUESTS, AiInteractionLogMapper::VIRTUAL_COLUMN_TOTAL_REQUESTS)
            ->withColumn(
                sprintf(static::SQL_TOTAL_TOKENS, SpyAiInteractionLogTableMap::COL_INPUT_TOKENS, SpyAiInteractionLogTableMap::COL_OUTPUT_TOKENS),
                AiInteractionLogMapper::VIRTUAL_COLUMN_TOTAL_TOKENS,
            )
            ->withColumn(
                sprintf(static::SQL_SUCCESS_COUNT, SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL),
                AiInteractionLogMapper::VIRTUAL_COLUMN_SUCCESS_COUNT,
            )
            ->withColumn(
                sprintf(static::SQL_AVERAGE_INFERENCE_TIME_MS, SpyAiInteractionLogTableMap::COL_INFERENCE_TIME_MS),
                AiInteractionLogMapper::VIRTUAL_COLUMN_AVERAGE_INFERENCE_TIME_MS,
            );

        /** @var array<string, mixed>|null $row */
        $row = $aiInteractionLogQuery->select([
            AiInteractionLogMapper::VIRTUAL_COLUMN_TOTAL_REQUESTS,
            AiInteractionLogMapper::VIRTUAL_COLUMN_TOTAL_TOKENS,
            AiInteractionLogMapper::VIRTUAL_COLUMN_SUCCESS_COUNT,
            AiInteractionLogMapper::VIRTUAL_COLUMN_AVERAGE_INFERENCE_TIME_MS,
        ])->findOne();

        $aiInteractionLogAggregationTransfer = new AiInteractionLogAggregationTransfer();

        if ($row === null) {
            return $aiInteractionLogAggregationTransfer;
        }

        return $this->getFactory()
            ->createAiInteractionLogMapper()
            ->mapAggregationRowToAiInteractionLogAggregationTransfer(
                $row,
                $aiInteractionLogAggregationTransfer,
            );
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

    protected function applyAiInteractionLogFilters(
        SpyAiInteractionLogQuery $aiInteractionLogQuery,
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): SpyAiInteractionLogQuery {
        $conditions = $aiInteractionLogCriteriaTransfer->getAiInteractionLogConditions();

        if ($conditions === null) {
            return $aiInteractionLogQuery;
        }

        if ($conditions->getAiInteractionLogIds()) {
            $aiInteractionLogQuery->filterByIdAiInteractionLog_In(
                $conditions->getAiInteractionLogIds(),
            );
        }

        if ($conditions->getConfigurationNames()) {
            $aiInteractionLogQuery->filterByConfigurationName_In(
                $conditions->getConfigurationNames(),
            );
        }

        if ($conditions->getIsSuccessful() !== null) {
            $aiInteractionLogQuery->filterByIsSuccessful(
                $conditions->getIsSuccessful(),
            );
        }

        if ($conditions->getConversationReferences()) {
            $aiInteractionLogQuery->filterByConversationReference_In(
                $conditions->getConversationReferences(),
            );
        }

        if ($conditions->getCreatedAtFrom() !== null) {
            $aiInteractionLogQuery->filterByCreatedAt(
                $conditions->getCreatedAtFrom(),
                Criteria::GREATER_EQUAL,
            );
        }

        if ($conditions->getCreatedAtTo() !== null) {
            $aiInteractionLogQuery->filterByCreatedAt(
                $conditions->getCreatedAtTo(),
                Criteria::LESS_EQUAL,
            );
        }

        return $aiInteractionLogQuery;
    }
}
