<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Creator;

use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;

interface AiWorkflowItemCreatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;
}
