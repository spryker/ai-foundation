<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapper;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolver;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\Kernel\AbstractFactory;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationFactory extends AbstractFactory
{
    public function createVendorAdapter(): VendorAdapterInterface
    {
        return $this->getVendorAdapterPlugin()->getVendorAdapter();
    }

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

    /**
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
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
