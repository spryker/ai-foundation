<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Service\AiFoundation\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;

interface AiInteractionCostCalculatorInterface
{
    /**
     * Specification:
     * - Calculates the estimated cost from input/output token counts and the given model price.
     * - The price is expressed per `$tokensPerPriceUnit` tokens; the result is rounded to `$precision` decimals.
     * - Returns null when the model price is unpriced.
     */
    public function calculateCost(
        AiModelPriceTransfer $aiModelPriceTransfer,
        int $inputTokens,
        int $outputTokens,
        int $tokensPerPriceUnit,
        int $precision
    ): ?float;
}
