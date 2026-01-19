<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Plugin\StateMachine\Condition;

use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\StateMachine\Dependency\Plugin\ConditionPluginInterface;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 */
class OryxAgentSuccessConditionPlugin extends AbstractPlugin implements ConditionPluginInterface
{
    protected const string CONTEXT_KEY_SUCCESS = 'success';

    protected const string CONTEXT_KEY_ERROR = 'error';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\StateMachineItemTransfer $stateMachineItemTransfer
     *
     * @return bool
     */
    public function check(StateMachineItemTransfer $stateMachineItemTransfer): bool
    {
        $stateMachineItemTransfer->getIdentifierOrFail();

        $aiWorkflowItemTransfer = $this->loadWorkflowItem($stateMachineItemTransfer);

        if ($aiWorkflowItemTransfer === null) {
            return false;
        }

        return $this->checkSuccess($aiWorkflowItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\StateMachineItemTransfer $stateMachineItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer|null
     */
    protected function loadWorkflowItem(StateMachineItemTransfer $stateMachineItemTransfer): ?AiWorkflowItemTransfer
    {
        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->addAiWorkflowItemId($stateMachineItemTransfer->getIdentifierOrFail());

        $aiWorkflowItemCollection = $this->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        return $aiWorkflowItemCollection->getAiWorkflowItems()->count() > 0
            ? $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0)
            : null;
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return bool
     */
    protected function checkSuccess(AiWorkflowItemTransfer $aiWorkflowItemTransfer): bool
    {
        /** @var array<string, mixed> $contextData */
        $contextData = $aiWorkflowItemTransfer->getContextData();

        return $this->hasSuccessfulResponse($contextData) && !$this->hasError($contextData);
    }

    /**
     * @param array<string, mixed> $contextData
     *
     * @return bool
     */
    protected function hasSuccessfulResponse(array $contextData): bool
    {
        return (bool)($contextData[static::CONTEXT_KEY_SUCCESS] ?? false);
    }

    /**
     * @param array<string, mixed> $contextData
     *
     * @return bool
     */
    protected function hasError(array $contextData): bool
    {
        return isset($contextData[static::CONTEXT_KEY_ERROR]);
    }
}
