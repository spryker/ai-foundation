<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollector;
use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface;
use Spryker\Client\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\AiFoundation\Prompt\PromptSender;
use Spryker\Client\AiFoundation\Prompt\PromptSenderInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactory;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactoryInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\ClientNeuronAiToolMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\ClientNeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\StreamChunkMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\StreamChunkMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ClientProviderResolver;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ClientProviderResolverInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStub;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationFactory extends AbstractFactory
{
    public function createPromptSender(): PromptSenderInterface
    {
        return new PromptSender($this->createZedAiFoundationStub());
    }

    public function createZedAiFoundationStub(): AiFoundationStubInterface
    {
        return new AiFoundationStub($this->getZedRequestClient());
    }

    public function createGatewayChatHistoryFactory(): GatewayChatHistoryFactoryInterface
    {
        return new GatewayChatHistoryFactory($this->createZedAiFoundationStub());
    }

    public function getVendorAdapterPlugin(): VendorProviderPluginInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER);
    }

    public function createVendorAiAdapter(): VendorAdapterInterface
    {
        return new NeuronVendorAiAdapter(
            $this->createZedAiFoundationStub(),
            $this->createGatewayChatHistoryFactory(),
            $this->createClientProviderResolver(),
            $this->createNeuronAiMessageMapper(),
            $this->createClientNeuronAiToolMapper(),
            $this->createStreamChunkMapper(),
            $this->getAiToolPlugins(),
            $this->getPostPromptPlugins(),
            $this->getPreToolCallPlugins(),
            $this->getPostToolCallPlugins(),
            $this->getStreamEventPlugins(),
        );
    }

    public function createNeuronAiMessageMapper(): NeuronAiMessageMapperInterface
    {
        return new NeuronAiMessageMapper();
    }

    public function createClientNeuronAiToolMapper(): ClientNeuronAiToolMapperInterface
    {
        return new ClientNeuronAiToolMapper();
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface>
     */
    public function getAiToolPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_TOOL);
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface>
     */
    public function getPreToolCallPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_PRE_TOOL_CALL);
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface>
     */
    public function getPostToolCallPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_POST_TOOL_CALL);
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface>
     */
    public function getPostPromptPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT);
    }

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface>
     */
    public function getStreamEventPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_STREAM_EVENT);
    }

    public function createClientProviderResolver(): ClientProviderResolverInterface
    {
        return new ClientProviderResolver();
    }

    public function createStreamChunkMapper(): StreamChunkMapperInterface
    {
        return new StreamChunkMapper();
    }

    public function createAiInteractionLogCollector(): AiInteractionLogCollectorInterface
    {
        return new AiInteractionLogCollector(
            $this->createZedAiFoundationStub(),
        );
    }

    public function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::CLIENT_ZED_REQUEST);
    }
}
