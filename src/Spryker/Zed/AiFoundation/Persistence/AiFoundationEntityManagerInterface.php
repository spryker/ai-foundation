<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;

interface AiFoundationEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    /**
     * @param int $idAiWorkflowItem
     *
     * @return void
     */
    public function deleteAiWorkflowItem(int $idAiWorkflowItem): void;
}
