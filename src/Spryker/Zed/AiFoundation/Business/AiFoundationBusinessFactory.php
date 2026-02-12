<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\DbChatHistoryResolver;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolver;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\VendorAdapterInterface;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface getRepository()
 */
class AiFoundationBusinessFactory extends AbstractBusinessFactory
{
    public function getVendorAdapterPlugin(): VendorProviderPluginInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::VENDOR_PROVIDER_PLUGIN);
    }

    // NeuronAi related methods start

    public function createNeuronAiVendorAdapter(): VendorAdapterInterface
    {
        return new NeuronVendorAiAdapter(
            providerResolver: $this->createNeuronAiProviderResolver(),
            messageMapper: $this->createNeuronAiMessageMapper(),
            toolMapper: $this->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createNeuronAiChatHistoryResolver(),
            aiConfigurations: $this->getConfig()->getAiConfigurations(),
            aiToolSetPlugins: $this->getAiToolSetPlugins(),
        );
    }

    public function createNeuronAiProviderResolver(): ProviderResolverInterface
    {
        return new ProviderResolver();
    }

    public function createNeuronAiMessageMapper(): NeuronAiMessageMapper
    {
        return new NeuronAiMessageMapper(
            transferJsonSchemaMapper: $this->createTransferToJsonSchemaMapper(),
        );
    }

    public function createNeuronAiToolMapper(): NeuronAiToolMapperInterface
    {
        return new NeuronAiToolMapper();
    }

    public function createNeuronAiChatHistoryResolver(): ChatHistoryResolverInterface
    {
        return new DbChatHistoryResolver(
            entityManager: $this->getEntityManager(),
            repository: $this->getRepository(),
            messageMapper: $this->createNeuronAiMessageMapper(),
            config: $this->getConfig(),
        );
    }

    /**
     * @return array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
     */
    public function getAiToolSetPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_TOOL_SET);
    }

    // NeuronAi related methods finish

    public function createTransferToJsonSchemaMapper(): TransferJsonSchemaMapperInterface
    {
        return new TransferJsonSchemaMapper();
    }
}
