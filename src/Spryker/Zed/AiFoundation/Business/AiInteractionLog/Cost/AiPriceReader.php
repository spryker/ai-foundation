<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;
use Spryker\Zed\AiFoundation\AiFoundationConfig;

class AiPriceReader implements AiPriceReaderInterface
{
    protected const string PRICE_KEY_INPUT = 'input';

    protected const string PRICE_KEY_OUTPUT = 'output';

    protected const string PRICE_KEY_CURRENCY = 'currency';

    public function __construct(protected AiFoundationConfig $aiFoundationConfig)
    {
    }

    public function getAiPriceMap(): AiPriceMapTransfer
    {
        $providerPricingTabMap = $this->aiFoundationConfig->getProviderPricingTabMap();

        $aiPriceMapTransfer = new AiPriceMapTransfer();

        foreach ($providerPricingTabMap as $provider => $providerTab) {
            $modelPrices = $this->aiFoundationConfig->getAiModelPricesForProviderTab((string)$providerTab);

            foreach ($modelPrices as $model => $priceEntry) {
                $aiPriceMapTransfer->addAiModelPrice(
                    $this->buildAiModelPrice((string)$provider, (string)$model, $priceEntry),
                );
            }
        }

        return $aiPriceMapTransfer;
    }

    public function resolveAiModelPrice(AiPriceMapTransfer $aiPriceMapTransfer, string $provider, string $model): AiModelPriceTransfer
    {
        foreach ($aiPriceMapTransfer->getAiModelPrices() as $aiModelPriceTransfer) {
            if ($aiModelPriceTransfer->getProvider() === $provider && $aiModelPriceTransfer->getModel() === $model) {
                return $aiModelPriceTransfer;
            }
        }

        return (new AiModelPriceTransfer())
            ->setProvider($provider)
            ->setModel($model)
            ->setIsPriced(false);
    }

    protected function buildAiModelPrice(string $provider, string $model, mixed $priceEntry): AiModelPriceTransfer
    {
        $inputPrice = $this->parsePrice(is_array($priceEntry) ? ($priceEntry[static::PRICE_KEY_INPUT] ?? null) : null);
        $outputPrice = $this->parsePrice(is_array($priceEntry) ? ($priceEntry[static::PRICE_KEY_OUTPUT] ?? null) : null);

        return (new AiModelPriceTransfer())
            ->setProvider($provider)
            ->setModel($model)
            ->setInputPricePerMillion($inputPrice)
            ->setOutputPricePerMillion($outputPrice)
            ->setCurrency($this->parseCurrency(is_array($priceEntry) ? ($priceEntry[static::PRICE_KEY_CURRENCY] ?? null) : null))
            ->setIsPriced($inputPrice !== null && $outputPrice !== null);
    }

    protected function parseCurrency(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $currency = trim($value);

        return $currency === '' ? null : $currency;
    }

    protected function parsePrice(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $price = (float)$value;

        // A negative price is invalid; treat it as unconfigured rather than as a 0-cost interaction.
        if ($price < 0.0) {
            return null;
        }

        return $price;
    }
}
