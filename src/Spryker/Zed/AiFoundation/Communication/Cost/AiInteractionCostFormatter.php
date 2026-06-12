<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;
use Spryker\Service\AiFoundation\AiFoundationServiceInterface;
use Spryker\Zed\AiFoundation\AiFoundationConfig;

class AiInteractionCostFormatter implements AiInteractionCostFormatterInterface
{
    protected const string UNPRICED_LABEL = 'N/A';

    protected const string COST_WITH_CURRENCY_FORMAT = '%s %s';

    public function __construct(
        protected AiFoundationServiceInterface $aiFoundationService,
        protected AiFoundationConfig $aiFoundationConfig,
    ) {
    }

    public function formatRowCost(
        AiPriceMapTransfer $aiPriceMapTransfer,
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
    ): string {
        $aiModelPriceTransfer = $this->findAiModelPrice($aiPriceMapTransfer, $provider, $model);

        if ($aiModelPriceTransfer === null) {
            return static::UNPRICED_LABEL;
        }

        // The cost formula lives only in the Service-layer calculator; the formatter delegates and is responsible for display only.
        $cost = $this->aiFoundationService->calculateAiInteractionCost(
            $aiModelPriceTransfer,
            $inputTokens,
            $outputTokens,
            $this->aiFoundationConfig->getTokensPerPriceUnit(),
            $this->aiFoundationConfig->getCostRoundingPrecisionForRow(),
        );

        if ($cost === null) {
            return static::UNPRICED_LABEL;
        }

        $formattedCost = number_format($cost, $this->aiFoundationConfig->getCostRoundingPrecisionForRow());

        return $this->appendCurrency($formattedCost, $aiModelPriceTransfer->getCurrency());
    }

    protected function appendCurrency(string $formattedCost, ?string $currency): string
    {
        // Currency is optional: when configured it is appended as a plain code (no symbol); otherwise only the price is shown.
        if ($currency === null || $currency === '') {
            return $formattedCost;
        }

        return sprintf(static::COST_WITH_CURRENCY_FORMAT, $formattedCost, $currency);
    }

    protected function findAiModelPrice(AiPriceMapTransfer $aiPriceMapTransfer, string $provider, string $model): ?AiModelPriceTransfer
    {
        foreach ($aiPriceMapTransfer->getAiModelPrices() as $aiModelPriceTransfer) {
            if ($aiModelPriceTransfer->getProvider() === $provider && $aiModelPriceTransfer->getModel() === $model) {
                return $aiModelPriceTransfer;
            }
        }

        return null;
    }
}
