<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver;

use NeuronAI\Providers\AIProviderInterface;

interface ClientProviderResolverInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @throws \Spryker\Client\AiFoundation\Exception\ProviderNotResolvableException
     */
    public function resolve(string $providerName, array $config): AIProviderInterface;
}
