<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation;

use Codeception\Actor;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 *
 * @SuppressWarnings(PHPMD)
 */
class AiFoundationCommunicationTester extends Actor
{
    use _generated\AiFoundationCommunicationTesterActions;

    /**
     * Creates a test state machine process and state for AI Workflow testing.
     *
     * @return int The ID of the created state machine item state
     */
    public function haveAiWorkflowStateMachineState(): int
    {
        $stateMachineProcessEntity = $this->haveStateMachineProcess([
            'stateMachineName' => 'AiWorkflow',
            'processName' => sprintf('TestProcess_%s', uniqid()),
        ]);

        $stateMachineItemStateTransfer = $this->haveStateMachineItemState([
            'name' => sprintf('test_state_%s', uniqid()),
            'fkStateMachineProcess' => $stateMachineProcessEntity->getIdStateMachineProcess(),
        ]);

        return (int)$stateMachineItemStateTransfer->getIdStateMachineItemState();
    }

    /**
     * Creates a test AI workflow item in the database.
     *
     * @param array<string, mixed> $seed
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function haveAiWorkflowItem(array $seed = []): AiWorkflowItemTransfer
    {
        $contextData = $seed['context_data'] ?? ['test' => 'data'];
        $fkStateMachineItemState = $seed['fk_state_machine_item_state'] ?? null;

        $workflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setContextData($contextData)
            ->setFkStateMachineItemState($fkStateMachineItemState);

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($workflowItemTransfer);

        $response = $this->getFacade()->createAiWorkflowItemCollection($request);

        return $response->getAiWorkflowItems()->offsetGet(0);
    }
}
