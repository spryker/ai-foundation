<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\AiFoundation\AiFoundationConstants;

class AiFoundationConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAiConfigurations(): array
    {
        return $this->get(AiFoundationConstants::AI_CONFIGURATIONS);
    }
}
