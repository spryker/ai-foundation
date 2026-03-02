<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\StateMachineHandler;

use Generated\Shared\Transfer\StateMachineItemTransfer;

interface AiWorkflowStateMachineItemUpdaterInterface
{
    public function updateAiWorkflowItemState(StateMachineItemTransfer $stateMachineItemTransfer): bool;
}
