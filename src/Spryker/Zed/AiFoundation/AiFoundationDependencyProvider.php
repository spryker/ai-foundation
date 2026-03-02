<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Zed\AiFoundation\Communication\Plugin\AiFoundation\NeuronAiVendorProviderPlugin;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class AiFoundationDependencyProvider extends AbstractBundleDependencyProvider
{
    public const VENDOR_PROVIDER_PLUGIN = 'VENDOR_PROVIDER_PLUGIN';

    public const PLUGINS_AI_TOOL_SET = 'PLUGINS_AI_TOOL_SET';

    public const PLUGINS_AI_WORKFLOW_COMMAND = 'PLUGINS_AI_WORKFLOW_COMMAND';

    public const PLUGINS_AI_WORKFLOW_CONDITION = 'PLUGINS_AI_WORKFLOW_CONDITION';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addVendorAdapterPlugin($container);
        $container = $this->addAiToolSetPlugins($container);

        return $container;
    }

    protected function addVendorAdapterPlugin(Container $container): Container
    {
        $container->set(static::VENDOR_PROVIDER_PLUGIN, function (): VendorProviderPluginInterface {
            return $this->getVendorAdapterPlugin();
        });

        return $container;
    }

    protected function getVendorAdapterPlugin(): VendorProviderPluginInterface
    {
        return new NeuronAiVendorProviderPlugin();
    }

    protected function addAiToolSetPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_TOOL_SET, function (): array {
            return $this->getAiToolSetPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
     */
    protected function getAiToolSetPlugins(): array
    {
        return [];
    }

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addAiWorkflowCommandPlugins($container);
        $container = $this->addAiWorkflowConditionPlugins($container);

        return $container;
    }

    protected function addAiWorkflowCommandPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_WORKFLOW_COMMAND, function (): array {
            return $this->getAiWorkflowCommandPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\CommandPluginInterface>
     */
    protected function getAiWorkflowCommandPlugins(): array
    {
        return [];
    }

    protected function addAiWorkflowConditionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_WORKFLOW_CONDITION, function (): array {
            return $this->getAiWorkflowConditionPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\ConditionPluginInterface>
     */
    protected function getAiWorkflowConditionPlugins(): array
    {
        return [];
    }
}
