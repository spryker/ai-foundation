<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;

class AiWorkflowItemWriter implements AiWorkflowItemWriterInterface
{
    /**
     * @param \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface $aiWorkflowItemEntityManager
     */
    public function __construct(protected AiFoundationEntityManagerInterface $aiWorkflowItemEntityManager)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        return $this->aiWorkflowItemEntityManager->createAiWorkflowItem($aiWorkflowItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemState(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemTransfer->requireIdAiWorkflowItem();
        $aiWorkflowItemTransfer->requireFkStateMachineItemState();

        return $this->aiWorkflowItemEntityManager->updateAiWorkflowItem($aiWorkflowItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemContext(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemTransfer->requireIdAiWorkflowItem();
        $aiWorkflowItemTransfer->requireContextData();

        return $this->aiWorkflowItemEntityManager->updateAiWorkflowItem($aiWorkflowItemTransfer);
    }
}
