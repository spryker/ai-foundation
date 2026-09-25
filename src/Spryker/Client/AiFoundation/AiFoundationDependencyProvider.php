<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\AiFoundation\Plugin\AiFoundation\NeuronAiVendorProviderPlugin;
use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationDependencyProvider extends AbstractDependencyProvider
{
    public const CLIENT_ZED_REQUEST = 'CLIENT_ZED_REQUEST';

    public const string PLUGIN_VENDOR_PROVIDER = 'PLUGIN_VENDOR_PROVIDER';

    public const string PLUGINS_AI_TOOL = 'PLUGINS_AI_TOOL';

    public const string PLUGINS_PRE_TOOL_CALL = 'PLUGINS_PRE_TOOL_CALL';

    public const string PLUGINS_POST_TOOL_CALL = 'PLUGINS_POST_TOOL_CALL';

    public const string PLUGINS_POST_PROMPT = 'PLUGINS_POST_PROMPT';

    public const string PLUGINS_STREAM_EVENT = 'PLUGINS_STREAM_EVENT';

    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);
        $container = $this->addZedRequestClient($container);
        $container = $this->addVendorAdapterPlugin($container);
        $container = $this->addClientAiToolPlugins($container);
        $container = $this->addPreToolCallPlugins($container);
        $container = $this->addPostToolCallPlugins($container);
        $container = $this->addPostPromptPlugins($container);
        $container = $this->addStreamEventPlugins($container);

        return $container;
    }

    protected function addZedRequestClient(Container $container): Container
    {
        $container->set(static::CLIENT_ZED_REQUEST, function (Container $container) {
            return $container->getLocator()->zedRequest()->client();
        });

        return $container;
    }

    protected function addVendorAdapterPlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_VENDOR_PROVIDER, function (): VendorProviderPluginInterface {
            return $this->getVendorAdapterPlugin();
        });

        return $container;
    }

    protected function getVendorAdapterPlugin(): VendorProviderPluginInterface
    {
        return new NeuronAiVendorProviderPlugin();
    }

    protected function addClientAiToolPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AI_TOOL, function (Container $container): array {
            return $this->getAiToolPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface>
     */
    protected function getAiToolPlugins(): array
    {
        return [];
    }

    protected function addPreToolCallPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRE_TOOL_CALL, function (Container $container): array {
            return $this->getPreToolCallPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface>
     */
    protected function getPreToolCallPlugins(): array
    {
        return [];
    }

    protected function addPostToolCallPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_POST_TOOL_CALL, function (Container $container): array {
            return $this->getPostToolCallPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface>
     */
    protected function getPostToolCallPlugins(): array
    {
        return [];
    }

    protected function addPostPromptPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_POST_PROMPT, function (Container $container): array {
            return $this->getPostPromptPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface>
     */
    protected function getPostPromptPlugins(): array
    {
        return [];
    }

    protected function addStreamEventPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_STREAM_EVENT, function (Container $container): array {
            return $this->getStreamEventPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface>
     */
    protected function getStreamEventPlugins(): array
    {
        return [];
    }
}
