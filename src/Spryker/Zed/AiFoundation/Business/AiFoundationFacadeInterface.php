<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;

interface AiFoundationFacadeInterface
{
    /**
     * Specification:
     * - Creates a new AI workflow item.
     * - Persists the item to the database.
     * - Serializes contextData to JSON.
     * - Returns the created transfer with generated ID.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    /**
     * Specification:
     * - Updates an existing AI workflow item state.
     * - Updates fkStateMachineItemState field.
     * - Persists changes to the database.
     * - Returns the updated transfer.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemState(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    /**
     * Specification:
     * - Updates AI workflow item context data only.
     * - Serializes contextData array to JSON.
     * - Does not modify state or other fields.
     * - Returns the updated transfer.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemContext(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    /**
     * Specification:
     * - Retrieves AI workflow items based on provided criteria.
     * - Filters by workflow item IDs if aiWorkflowItemIds is provided in criteria.
     * - Filters by state IDs if stateIds is provided in criteria.
     * - Deserializes contextData to array for each item.
     * - Returns collection of workflow items matching the criteria.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer;
}
