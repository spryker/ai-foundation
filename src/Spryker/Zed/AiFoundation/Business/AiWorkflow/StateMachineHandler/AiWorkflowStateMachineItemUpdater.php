<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\StateMachineHandler;

use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class AiWorkflowStateMachineItemUpdater implements AiWorkflowStateMachineItemUpdaterInterface
{
    public function __construct(
        protected AiFoundationRepositoryInterface $repository,
        protected AiFoundationEntityManagerInterface $entityManager,
    ) {
    }

    public function updateAiWorkflowItemState(StateMachineItemTransfer $stateMachineItemTransfer): bool
    {
        $idAiWorkflowItem = $stateMachineItemTransfer->getIdentifierOrFail();

        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addAiWorkflowItemId($idAiWorkflowItem);

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollection = $this->repository->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        if ($aiWorkflowItemCollection->getAiWorkflowItems()->count() === 0) {
            return false;
        }

        $aiWorkflowItemTransfer = $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0);
        $aiWorkflowItemTransfer->setFkStateMachineItemState((int)$stateMachineItemTransfer->getIdItemState());

        $this->entityManager->updateAiWorkflowItem($aiWorkflowItemTransfer);

        return true;
    }
}
