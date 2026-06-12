<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;

interface AiInteractionCostCalculatorInterface
{
    /**
     * Specification:
     * - Calculates the estimated USD cost of a single interaction, rounded to the per-row precision.
     * - Returns null when the price is not set (the interaction is unpriced and must be shown as "N/A").
     */
    public function calculateRowCost(int $inputTokens, int $outputTokens, AiModelPriceTransfer $aiModelPriceTransfer): ?float;

    /**
     * Specification:
     * - Calculates the estimated USD cost of an aggregated provider/model group, rounded to the aggregate precision.
     * - Returns null when the price is not set (the group is unpriced and must be excluded from totals).
     */
    public function calculateBreakdownCost(int $inputTokens, int $outputTokens, AiModelPriceTransfer $aiModelPriceTransfer): ?float;
}
