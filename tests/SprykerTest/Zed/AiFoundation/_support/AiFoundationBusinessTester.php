<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation;

use Codeception\Actor;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog;

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
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getFactory()
 *
 * @SuppressWarnings(PHPMD)
 */
class AiFoundationBusinessTester extends Actor
{
    use _generated\AiFoundationBusinessTesterActions;

    /**
     * @param array<string, mixed> $override
     */
    public function haveAiInteractionLog(array $override = []): SpyAiInteractionLog
    {
        $defaults = [
            'ConfigurationName' => sprintf('test_config_%s', uniqid()),
            'provider' => 'test_provider',
            'model' => 'test_model',
            'prompt' => 'test prompt',
            'response' => 'test response',
            'input_tokens' => 100,
            'output_tokens' => 50,
            'inference_time_ms' => 500,
            'is_successful' => true,
            'conversation_reference' => sprintf('conv_%s', uniqid()),
        ];

        $data = array_merge($defaults, $override);
        $entity = new SpyAiInteractionLog();
        $entity->fromArray($data);
        $entity->save();

        return $entity;
    }

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
}
