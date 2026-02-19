<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\StateMachineHandler;

use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class AiWorkflowStateMachineItemReader implements AiWorkflowStateMachineItemReaderInterface
{
    public function __construct(protected AiFoundationRepositoryInterface $repository)
    {
    }

    /**
     * @param array<int> $stateIds
     *
     * @return array<\Generated\Shared\Transfer\StateMachineItemTransfer>
     */
    public function getStateMachineItemsByStateIds(array $stateIds): array
    {
        if ($stateIds === []) {
            return [];
        }

        $aiWorkflowItemConditionsTransfer = new AiWorkflowItemConditionsTransfer();

        foreach ($stateIds as $stateId) {
            $aiWorkflowItemConditionsTransfer->addStateId($stateId);
        }

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollection = $this->repository->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        $stateMachineItemTransfers = [];

        foreach ($aiWorkflowItemCollection->getAiWorkflowItems() as $aiWorkflowItem) {
            $stateMachineItemTransfers[] = (new StateMachineItemTransfer())
                ->setIdentifier($aiWorkflowItem->getIdAiWorkflowItem())
                ->setIdItemState($aiWorkflowItem->getFkStateMachineItemState());
        }

        return $stateMachineItemTransfers;
    }
}
