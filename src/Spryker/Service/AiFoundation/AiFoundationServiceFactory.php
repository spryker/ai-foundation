<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Service\AiFoundation;

use Spryker\Service\AiFoundation\Cost\AiInteractionCostCalculator;
use Spryker\Service\AiFoundation\Cost\AiInteractionCostCalculatorInterface;
use Spryker\Service\Kernel\AbstractServiceFactory;

class AiFoundationServiceFactory extends AbstractServiceFactory
{
    public function createAiInteractionCostCalculator(): AiInteractionCostCalculatorInterface
    {
        return new AiInteractionCostCalculator();
    }
}
