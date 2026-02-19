<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Plugin\StateMachine;

use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\StateMachine\Dependency\Plugin\StateMachineHandlerInterface;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getBusinessFactory()
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
        return $this->getConfig()->getAiWorkflowStateMachineName();
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
        return $this->getBusinessFactory()
            ->createAiWorkflowStateMachineItemUpdater()
            ->updateAiWorkflowItemState($stateMachineItemTransfer);
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
        return $this->getBusinessFactory()
            ->createAiWorkflowStateMachineItemReader()
            ->getStateMachineItemsByStateIds($stateIds);
    }
}
