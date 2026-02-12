<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver;

use NeuronAI\Providers\AIProviderInterface;

interface ProviderResolverInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @return \NeuronAI\Providers\AIProviderInterface
     */
    public function resolve(string $providerName, array $config): AIProviderInterface;
}
