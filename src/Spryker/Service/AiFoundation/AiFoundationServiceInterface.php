<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Service\AiFoundation;

use Generated\Shared\Transfer\AiModelPriceTransfer;

interface AiFoundationServiceInterface
{
    /**
     * Specification:
     * - Calculates the estimated cost of an AI interaction from its input/output token counts and the given model price.
     * - The price is expressed per `$tokensPerPriceUnit` tokens (e.g. 1,000,000).
     * - The result is rounded to `$precision` decimal places.
     * - Returns null when the model price is unpriced, so callers can render it as "N/A" and exclude it from totals.
     *
     * @api
     */
    public function calculateAiInteractionCost(
        AiModelPriceTransfer $aiModelPriceTransfer,
        int $inputTokens,
        int $outputTokens,
        int $tokensPerPriceUnit,
        int $precision
    ): ?float;
}
