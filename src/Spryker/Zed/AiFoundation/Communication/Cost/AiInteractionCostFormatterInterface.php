<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Cost;

use Generated\Shared\Transfer\AiPriceMapTransfer;

interface AiInteractionCostFormatterInterface
{
    /**
     * Specification:
     * - Formats the estimated cost of a single interaction to the configured row precision for display.
     * - Appends the currency code as a plain string in the format '%s %s' (e.g. "0.0075 USD") when a currency is configured.
     * - Returns just the formatted number when no currency is configured.
     * - Returns the unpriced label ('N/A') when the model is unpriced or has no configured price.
     */
    public function formatRowCost(
        AiPriceMapTransfer $aiPriceMapTransfer,
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
    ): string;
}
