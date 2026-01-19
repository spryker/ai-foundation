<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class AiFoundationConfig extends AbstractBundleConfig
{
    protected const string AI_WORKFLOW_STATE_MACHINE_NAME = AiFoundationConstants::AI_WORKFLOW_STATE_MACHINE_NAME;

    protected const string DEFAULT_PROCESS_NAME_INTELLIGENT_TASK = 'IntelligentTask01';

    protected const string DEFAULT_INITIAL_STATE = 'new';

    protected const array PROCESS_NAME_TO_INITIAL_STATE_MAP = [
        self::DEFAULT_PROCESS_NAME_INTELLIGENT_TASK => self::DEFAULT_INITIAL_STATE,
    ];

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
     * @api
     *
     * @return array<string>
     */
    public function getActiveProcesses(): array
    {
        return array_merge([
            static::DEFAULT_PROCESS_NAME_INTELLIGENT_TASK,
        ], $this->getProjectActiveProcesses());
    }

    /**
     * @api
     *
     * @return string
     */
    public function getInitialStateForProcess(string $process): string
    {
        $projectInitialStateMap = $this->getProjectInitialStateMapForProcess();

        if (isset($projectInitialStateMap[$process])) {
            return $projectInitialStateMap[$process];
        }

        return static::PROCESS_NAME_TO_INITIAL_STATE_MAP[$process];
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
    public function getProjectActiveProcesses(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns a map of process names to their initial states for the project.
     * - Expected format: ['IntelligentTask01' => 'new'].
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getProjectInitialStateMapForProcess(): array
    {
        return [];
    }
}
