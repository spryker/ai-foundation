<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Service\AiFoundation\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;

class AiInteractionCostCalculator implements AiInteractionCostCalculatorInterface
{
    public function calculateCost(
        AiModelPriceTransfer $aiModelPriceTransfer,
        int $inputTokens,
        int $outputTokens,
        int $tokensPerPriceUnit,
        int $precision
    ): ?float {
        if (!$aiModelPriceTransfer->getIsPriced()) {
            return null;
        }

        $inputCost = $inputTokens / $tokensPerPriceUnit * (float)$aiModelPriceTransfer->getInputPricePerMillion();
        $outputCost = $outputTokens / $tokensPerPriceUnit * (float)$aiModelPriceTransfer->getOutputPricePerMillion();

        return round($inputCost + $outputCost, $precision);
    }
}
