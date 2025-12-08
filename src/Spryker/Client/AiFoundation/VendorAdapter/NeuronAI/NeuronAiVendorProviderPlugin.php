<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI;

use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationFactory getFactory()
 */
class NeuronAiVendorProviderPlugin extends AbstractPlugin implements VendorProviderPluginInterface
{
    public function getVendorAdapter(): VendorAdapterInterface
    {
        return $this->getFactory()->createNeuronAiVendorAdapter();
    }
}
