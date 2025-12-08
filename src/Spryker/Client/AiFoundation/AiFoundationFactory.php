<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
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
        return $this->getProvidedDependency(AiFoundationDependencyProvider::VENDOR_PROVIDEER_PLUGIN);
    }

    // NeuronAi related methods start

    public function createNeuronAiVendorAdapter(): VendorAdapterInterface
    {
        return new NeuronVendorAiAdapter(
            providerResolver: $this->createNeuronAiProviderResolver(),
            messageMapper: $this->createNeuronAiMessageMapper(),
            aiConfigurations: $this->getConfig()->getAiConfigurations(),
        );
    }

    public function createNeuronAiProviderResolver(): ProviderResolverInterface
    {
        return new ProviderResolver();
    }

    public function createNeuronAiMessageMapper(): NeuronAiMessageMapper
    {
        return new NeuronAiMessageMapper();
    }

    // NeuronAi related methods start
}
