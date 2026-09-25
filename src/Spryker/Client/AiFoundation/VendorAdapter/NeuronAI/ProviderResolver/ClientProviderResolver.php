<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver;

use NeuronAI\Providers\AIProviderInterface;
use Spryker\Client\AiFoundation\Exception\ProviderNotResolvableException;
use Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\AbstractProviderResolver;
use Throwable;

class ClientProviderResolver extends AbstractProviderResolver implements ClientProviderResolverInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @throws \Spryker\Client\AiFoundation\Exception\ProviderNotResolvableException
     */
    public function resolve(string $providerName, array $config): AIProviderInterface
    {
        if ($providerName === '') {
            throw $this->createProviderNotResolvableException('Provider name must not be empty.');
        }

        return parent::resolve($providerName, $config);
    }

    protected function createProviderNotResolvableException(string $message): Throwable
    {
        return new ProviderNotResolvableException($message);
    }
}
