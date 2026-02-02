<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Plugin\StateMachine;

use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\StateMachine\Dependency\Plugin\StateMachineHandlerInterface;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiWorkflowStateMachineHandlerPlugin extends AbstractPlugin implements StateMachineHandlerInterface
{
 /**
  * {@inheritDoc}
  *
  * @api
  *
  * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\CommandPluginInterface>
  */
    public function getCommandPlugins(): array
    {
        return $this->getFactory()->getAiWorkflowCommandPlugins();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\ConditionPluginInterface>
     */
    public function getConditionPlugins(): array
    {
        return $this->getFactory()->getAiWorkflowConditionPlugins();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getStateMachineName(): string
    {
        return $this->getConfig()->getStateMachineName();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string>
     */
    public function getActiveProcesses(): array
    {
        return $this->getConfig()->getAiWorkflowActiveProcesses();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $processName
     *
     * @return string
     */
    public function getInitialStateForProcess($processName): string
    {
        return $this->getConfig()->getAiWorkflowInitialStateForProcess($processName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\StateMachineItemTransfer $stateMachineItemTransfer
     *
     * @return bool
     */
    public function itemStateUpdated(StateMachineItemTransfer $stateMachineItemTransfer): bool
    {
        $idAiWorkflowItem = $stateMachineItemTransfer->getIdentifierOrFail();

        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addAiWorkflowItemId($idAiWorkflowItem);

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollection = $this->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        if ($aiWorkflowItemCollection->getAiWorkflowItems()->count() === 0) {
            return false;
        }

        $aiWorkflowItemTransfer = $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0);
        $aiWorkflowItemTransfer->setFkStateMachineItemState($stateMachineItemTransfer->getIdItemState());

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $response = $this->getFacade()->updateAiWorkflowItemStateCollection($request);

        return $response->getIsSuccessful() ?? false;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<int> $stateIds
     *
     * @return array<\Generated\Shared\Transfer\StateMachineItemTransfer>
     */
    public function getStateMachineItemsByStateIds(array $stateIds = []): array
    {
        if ($stateIds === []) {
            return $stateIds;
        }

        $aiWorkflowItemConditionsTransfer = new AiWorkflowItemConditionsTransfer();

        foreach ($stateIds as $stateId) {
            $aiWorkflowItemConditionsTransfer->addStateId($stateId);
        }

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollection = $this->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        $stateMachineItemTransfers = [];

        foreach ($aiWorkflowItemCollection->getAiWorkflowItems() as $aiWorkflowItem) {
            $stateMachineItemTransfer = (new StateMachineItemTransfer())
                ->setIdentifier($aiWorkflowItem->getIdAiWorkflowItem())
                ->setIdItemState($aiWorkflowItem->getFkStateMachineItemState());

            $stateMachineItemTransfers[] = $stateMachineItemTransfer;
        }

        return $stateMachineItemTransfers;
    }
}
