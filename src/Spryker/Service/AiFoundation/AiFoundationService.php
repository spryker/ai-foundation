<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Service\AiFoundation;

use Generated\Shared\Transfer\AiModelPriceTransfer;
use Spryker\Service\Kernel\AbstractService;

/**
 * @method \Spryker\Service\AiFoundation\AiFoundationServiceFactory getFactory()
 */
class AiFoundationService extends AbstractService implements AiFoundationServiceInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function calculateAiInteractionCost(
        AiModelPriceTransfer $aiModelPriceTransfer,
        int $inputTokens,
        int $outputTokens,
        int $tokensPerPriceUnit,
        int $precision
    ): ?float {
        return $this->getFactory()
            ->createAiInteractionCostCalculator()
            ->calculateCost($aiModelPriceTransfer, $inputTokens, $outputTokens, $tokensPerPriceUnit, $precision);
    }
}
