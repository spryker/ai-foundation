<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Reader;

use Generated\Shared\Transfer\AiCurrencyTotalTransfer;
use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Generated\Shared\Transfer\AiInteractionLogUnpricedCombinationTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;
use Generated\Shared\Transfer\AiPricingBreakdownTransfer;
use Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost\AiInteractionCostCalculatorInterface;
use Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost\AiPriceReaderInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class AiInteractionLogReader implements AiInteractionLogReaderInterface
{
    protected const int DEFAULT_RATE_VALUE = 0;

    protected const int ROUNDING_PRECISION = 2;

    public function __construct(
        protected AiFoundationRepositoryInterface $aiFoundationRepository,
        protected AiPriceReaderInterface $aiPriceReader,
        protected AiInteractionCostCalculatorInterface $aiInteractionCostCalculator,
    ) {
    }

    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionResponseTransfer {
        $collectionTransfer = $this->aiFoundationRepository->getAiInteractionLogCollection(
            $aiInteractionLogCriteriaTransfer,
        );

        return (new AiInteractionLogCollectionResponseTransfer())
            ->setAiInteractionLogCollection($collectionTransfer)
            ->setIsSuccessful(true);
    }

    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer {
        $aggregationTransfer = $this->aiFoundationRepository->getAiInteractionLogAggregation(
            $aiInteractionLogCriteriaTransfer,
        );

        $aggregationTransfer
            ->setSuccessRate($this->calculateSuccessRate($aggregationTransfer->getTotalRequests() ?? static::DEFAULT_RATE_VALUE, $aggregationTransfer->getSuccessCount() ?? static::DEFAULT_RATE_VALUE))
            ->setAverageInferenceTimeMs(round($aggregationTransfer->getAverageInferenceTimeMs() ?? static::DEFAULT_RATE_VALUE, static::ROUNDING_PRECISION));

        return $this->enrichAggregationWithCost($aggregationTransfer, $aiInteractionLogCriteriaTransfer);
    }

    public function getAiPriceMap(): AiPriceMapTransfer
    {
        return $this->aiPriceReader->getAiPriceMap();
    }

    protected function enrichAggregationWithCost(
        AiInteractionLogAggregationTransfer $aiInteractionLogAggregationTransfer,
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer {
        $aiPriceMapTransfer = $this->aiPriceReader->getAiPriceMap();
        $aiPricingBreakdownTransfers = $this->aiFoundationRepository->getAiInteractionLogPricingBreakdown(
            $aiInteractionLogCriteriaTransfer,
        );

        $totalEstimatedCost = 0.0;
        $hasUnpricedInteractions = false;

        // Per-currency running totals keyed by currency code (empty string key groups breakdowns with no configured currency).
        $currencyTotals = [];

        foreach ($aiPricingBreakdownTransfers as $aiPricingBreakdownTransfer) {
            $aiModelPriceTransfer = $this->aiPriceReader->resolveAiModelPrice(
                $aiPriceMapTransfer,
                (string)$aiPricingBreakdownTransfer->getProvider(),
                (string)$aiPricingBreakdownTransfer->getModel(),
            );

            $estimatedCost = $this->aiInteractionCostCalculator->calculateBreakdownCost(
                $aiPricingBreakdownTransfer->getInputTokensOrFail(),
                $aiPricingBreakdownTransfer->getOutputTokensOrFail(),
                $aiModelPriceTransfer,
            );

            // Unpriced combinations stay in the breakdown (rendered as an em-dash with an alert info icon)
            // while still being excluded from the totals and flagged for the card-level "interactions excluded" notice.
            if ($estimatedCost === null) {
                $hasUnpricedInteractions = true;
                $aiInteractionLogAggregationTransfer
                    ->addUnpricedCombination($this->buildUnpricedCombination($aiPricingBreakdownTransfer))
                    ->addPricingBreakdown($aiPricingBreakdownTransfer->setEstimatedCost(null));

                continue;
            }

            $aiPricingBreakdownTransfer
                ->setEstimatedCost($estimatedCost)
                ->setCurrency($aiModelPriceTransfer->getCurrency());
            // Accumulate the grand total from the already-rounded breakdown costs so the breakdown rows sum exactly to the displayed total.
            $totalEstimatedCost += $estimatedCost;
            $this->addToCurrencyTotal($currencyTotals, $aiModelPriceTransfer->getCurrency(), $estimatedCost);
            $aiInteractionLogAggregationTransfer->addPricingBreakdown($aiPricingBreakdownTransfer);
        }

        $this->applyCurrencyTotals($aiInteractionLogAggregationTransfer, $currencyTotals);

        return $aiInteractionLogAggregationTransfer
            ->setTotalEstimatedCost(round($totalEstimatedCost, static::ROUNDING_PRECISION))
            ->setHasUnpricedInteractions($hasUnpricedInteractions);
    }

    /**
     * @param array<string, array{currency: string|null, total: float}> $currencyTotals
     */
    protected function addToCurrencyTotal(array &$currencyTotals, ?string $currency, float $estimatedCost): void
    {
        $currencyKey = (string)$currency;

        if (!isset($currencyTotals[$currencyKey])) {
            $currencyTotals[$currencyKey] = ['currency' => $currency, 'total' => 0.0];
        }

        $currencyTotals[$currencyKey]['total'] += $estimatedCost;
    }

    /**
     * @param array<string, array{currency: string|null, total: float}> $currencyTotals
     */
    protected function applyCurrencyTotals(
        AiInteractionLogAggregationTransfer $aiInteractionLogAggregationTransfer,
        array $currencyTotals,
    ): void {
        foreach ($currencyTotals as $currencyTotal) {
            $aiInteractionLogAggregationTransfer->addCurrencyTotal(
                (new AiCurrencyTotalTransfer())
                    ->setCurrency($currencyTotal['currency'])
                    ->setTotalEstimatedCost(round($currencyTotal['total'], static::ROUNDING_PRECISION)),
            );
        }

        // The single grand-total currency is only meaningful when every priced interaction shares one currency; otherwise the per-currency totals carry the currency.
        if (count($currencyTotals) === 1) {
            $aiInteractionLogAggregationTransfer->setTotalCurrency(reset($currencyTotals)['currency']);
        }
    }

    protected function buildUnpricedCombination(
        AiPricingBreakdownTransfer $aiPricingBreakdownTransfer,
    ): AiInteractionLogUnpricedCombinationTransfer {
        return (new AiInteractionLogUnpricedCombinationTransfer())
            ->setProvider($aiPricingBreakdownTransfer->getProvider())
            ->setModel($aiPricingBreakdownTransfer->getModel())
            ->setRequestCount($aiPricingBreakdownTransfer->getRequestCount());
    }

    protected function calculateSuccessRate(int $totalRequests, int $successCount): float
    {
        if ($totalRequests === 0) {
            return (float)static::DEFAULT_RATE_VALUE;
        }

        return round($successCount * 100.0 / $totalRequests, static::ROUNDING_PRECISION);
    }
}
