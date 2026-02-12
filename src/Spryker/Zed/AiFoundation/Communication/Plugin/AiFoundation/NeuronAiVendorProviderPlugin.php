<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Plugin\AiFoundation;

use Spryker\Zed\AiFoundation\Business\VendorAdapter\VendorAdapterInterface;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class NeuronAiVendorProviderPlugin extends AbstractPlugin implements VendorProviderPluginInterface
{
    public function getVendorAdapter(): VendorAdapterInterface
    {
        return $this->getBusinessFactory()->createNeuronAiVendorAdapter();
    }
}
