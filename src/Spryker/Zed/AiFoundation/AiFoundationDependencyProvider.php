<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PLUGINS_AI_WORKFLOW_COMMAND = 'PLUGINS_AI_WORKFLOW_COMMAND';

    public const string PLUGINS_AI_WORKFLOW_CONDITION = 'PLUGINS_AI_WORKFLOW_CONDITION';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addAiWorkflowCommandPlugins($container);
        $container = $this->addAiWorkflowConditionPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addAiWorkflowCommandPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_WORKFLOW_COMMAND, function () {
            return $this->getAiWorkflowCommandPlugins();
        });

        return $container;
    }

    /**
     * Specification:
     * - Returns array of AI workflow command plugins.
     * - Example format: ['OryxAgent/Analyze' => new OryxAgentAnalyzeCommandPlugin()]
     *
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\CommandPluginInterface>
     */
    protected function getAiWorkflowCommandPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addAiWorkflowConditionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_WORKFLOW_CONDITION, function () {
            return $this->getAiWorkflowConditionPlugins();
        });

        return $container;
    }

    /**
     * Specification:
     * - Returns array of AI workflow condition plugins.
     * - Example format: ['OryxAgent/IsSuccessful' => new OryxAgentSuccessConditionPlugin()]
     *
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\ConditionPluginInterface>
     */
    protected function getAiWorkflowConditionPlugins(): array
    {
        return [];
    }
}
