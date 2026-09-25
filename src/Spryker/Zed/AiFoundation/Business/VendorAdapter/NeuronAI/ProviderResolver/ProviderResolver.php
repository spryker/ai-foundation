<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver;

use Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\AbstractProviderResolver;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Throwable;

class ProviderResolver extends AbstractProviderResolver implements ProviderResolverInterface
{
    protected function createProviderNotResolvableException(string $message): Throwable
    {
        return new NeuronAiConfigurationException($message);
    }
}
