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

interface AiFoundationRepositoryInterface
{
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;

    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer;

    /**
     * Retrieves AI interaction log collection filtered by criteria.
     */
    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionTransfer;

    /**
     * Returns aggregated statistics for AI interaction logs filtered by criteria.
     */
    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer;

    /**
     * Returns the per-provider/model token aggregation (request count, summed input/output/total tokens) for AI interaction logs filtered by criteria.
     *
     * @return array<\Generated\Shared\Transfer\AiPricingBreakdownTransfer>
     */
    public function getAiInteractionLogPricingBreakdown(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): array;
}
