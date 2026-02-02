<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Deleter;

use Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;

interface AiWorkflowItemDeleterInterface
{
    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function deleteAiWorkflowItemCollection(
        AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
    ): AiWorkflowItemCollectionResponseTransfer;
}
