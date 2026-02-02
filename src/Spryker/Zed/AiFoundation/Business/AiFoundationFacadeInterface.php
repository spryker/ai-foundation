<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;

interface AiFoundationFacadeInterface
{
    /**
     * Specification:
     * - Creates AI workflow items in collection.
     * - Validates all items before persistence (required fields, contextData format).
     * - Supports transactional operations (default: true).
     * - If transactional and validation fails, returns response with errors and no items persisted.
     * - If non-transactional, persists valid items and returns both valid and invalid items with errors.
     * - Serializes contextData array to JSON for each item.
     * - Returns collection response with created items and/or errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates AI workflow item states in collection.
     * - Validates all items: required fields (idAiWorkflowItem, fkStateMachineItemState), existence, state transitions.
     * - Supports transactional operations (default: true).
     * - If transactional and validation fails, returns response with errors and no items persisted.
     * - If non-transactional, updates valid items and returns both valid and invalid items with errors.
     * - Returns collection response with updated items and/or errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function updateAiWorkflowItemStateCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates AI workflow item context data in collection.
     * - Validates all items: required fields (idAiWorkflowItem, contextData), existence.
     * - Supports transactional operations (default: true).
     * - If transactional and validation fails, returns response with errors and no items persisted.
     * - If non-transactional, updates valid items and returns both valid and invalid items with errors.
     * - Serializes contextData array to JSON for each item.
     * - Returns collection response with updated items and/or errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function updateAiWorkflowItemContextCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

    /**
     * Specification:
     * - Deletes AI workflow items based on provided criteria.
     * - Filters by workflow item IDs if provided in criteria.
     * - Fetches items matching criteria.
     * - Deletes items in transaction (default: transactional).
     * - Returns collection response with deleted items.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function deleteAiWorkflowItemCollection(
        AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

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
