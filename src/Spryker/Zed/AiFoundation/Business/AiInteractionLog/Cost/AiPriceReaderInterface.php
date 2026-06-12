<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;

interface AiPriceReaderInterface
{
    /**
     * Specification:
     * - Reads the configured token prices for every mapped provider/model from configuration management (global scope).
     * - Each entry is marked priced only when both input and output prices are non-negative decimals; otherwise it is unpriced (prices null).
     * - Returns an entry per provider/model in the configuration key map, regardless of whether a price is set.
     */
    public function getAiPriceMap(): AiPriceMapTransfer;

    /**
     * Specification:
     * - Resolves the price entry for the given provider and model from a previously read price map.
     * - Returns an unpriced entry when the provider/model is not present in the map.
     */
    public function resolveAiModelPrice(AiPriceMapTransfer $aiPriceMapTransfer, string $provider, string $model): AiModelPriceTransfer;
}
