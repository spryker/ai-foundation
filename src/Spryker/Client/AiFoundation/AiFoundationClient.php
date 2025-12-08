<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationFactory getFactory()
 */
class AiFoundationClient extends AbstractClient implements AiFoundationClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer
    {
        return $this->getFactory()
            ->createVendorAdapter()
            ->prompt($promptRequest);
    }
}
