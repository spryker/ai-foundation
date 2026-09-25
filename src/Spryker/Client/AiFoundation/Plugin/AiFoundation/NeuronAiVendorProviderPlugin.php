<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Plugin\AiFoundation;

use Spryker\Client\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \Spryker\Client\AiFoundation\AiFoundationFactory getFactory()
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class NeuronAiVendorProviderPlugin extends AbstractPlugin implements VendorProviderPluginInterface
{
    public function getVendorAdapter(): VendorAdapterInterface
    {
        return $this->getFactory()->createVendorAiAdapter();
    }
}
