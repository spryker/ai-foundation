<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class AiFoundationConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Defines the AiWorkflow state machine name.
     *
     * @api
     *
     * @var string
     */
    public const AI_WORKFLOW_STATE_MACHINE_NAME = 'AiWorkflow';

    protected const string DEFAULT_INITIAL_STATE = 'new';

    /**
     * @api
     *
     * @return string
     */
    public function getStateMachineName(): string
    {
        return static::AI_WORKFLOW_STATE_MACHINE_NAME;
    }

    /**
     * Specification:
     * - Returns a list of active AI workflow processes for the project.
     * - Expected format: ['IntelligentTask01', 'IntelligentTask02'].
     *
     * @api
     *
     * @return array<string>
     */
    public function getAiWorkflowActiveProcesses(): array
    {
        return [];
    }

    /**
     * @api
     *
     * @return string
     */
    public function getAiWorkflowInitialStateForProcess(string $process): string
    {
        return $this->getAiWorkflowInitialStateMapForProcess()[$process] ?? static::DEFAULT_INITIAL_STATE;
    }

    /**
     * Specification:
     * - Returns a map of process names to their initial states for the project.
     * - Expected format: ['IntelligentTask01' => 'new'].
     *
     * @return array<string, string>
     */
    protected function getAiWorkflowInitialStateMapForProcess(): array
    {
        return [];
    }
}
