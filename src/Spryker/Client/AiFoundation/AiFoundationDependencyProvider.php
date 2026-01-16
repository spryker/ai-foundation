<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronAiVendorProviderPlugin;
use Spryker\Client\AiFoundation\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationDependencyProvider extends AbstractDependencyProvider
{
    public const VENDOR_PROVIDER_PLUGIN = 'VENDOR_PROVIDER_PLUGIN';

    public const PLUGINS_AI_TOOL_SET = 'PLUGINS_AI_TOOL_SET';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);
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

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addAiToolSetPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_TOOL_SET, function (): array {
            return $this->getAiToolSetPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
     */
    protected function getAiToolSetPlugins(): array
    {
        return [];
    }
}
