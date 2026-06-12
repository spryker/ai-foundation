<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Communication\Cost;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiModelPriceTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;
use Spryker\Service\AiFoundation\AiFoundationService;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Communication\Cost\AiInteractionCostFormatter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Communication
 * @group Cost
 * @group AiInteractionCostFormatterTest
 * Add your own group annotations below this line
 */
class AiInteractionCostFormatterTest extends Unit
{
    protected const string PROVIDER = 'openai';

    protected const string MODEL = 'gpt-4.1';

    protected function createAiInteractionCostFormatter(): AiInteractionCostFormatter
    {
        return new AiInteractionCostFormatter(new AiFoundationService(), new AiFoundationConfig());
    }

    protected function createPricedAiPriceMapTransfer(?string $currency = null): AiPriceMapTransfer
    {
        return (new AiPriceMapTransfer())->addAiModelPrice(
            (new AiModelPriceTransfer())
                ->setProvider(static::PROVIDER)
                ->setModel(static::MODEL)
                ->setInputPricePerMillion(2.5)
                ->setOutputPricePerMillion(10.0)
                ->setCurrency($currency)
                ->setIsPriced(true),
        );
    }

    public function testFormatRowCostReturnsBareCostForPricedModelWithoutCurrency(): void
    {
        // Act
        $formattedCost = $this->createAiInteractionCostFormatter()->formatRowCost(
            $this->createPricedAiPriceMapTransfer(),
            static::PROVIDER,
            static::MODEL,
            1000,
            500,
        );

        // Assert
        $this->assertSame('0.0075', $formattedCost);
    }

    public function testFormatRowCostAppendsCurrencyCodeWhenConfigured(): void
    {
        // Act
        $formattedCost = $this->createAiInteractionCostFormatter()->formatRowCost(
            $this->createPricedAiPriceMapTransfer('USD'),
            static::PROVIDER,
            static::MODEL,
            1000,
            500,
        );

        // Assert
        $this->assertSame('0.0075 USD', $formattedCost);
    }

    public function testFormatRowCostReturnsNaForUnpricedModel(): void
    {
        // Act
        $formattedCost = $this->createAiInteractionCostFormatter()->formatRowCost(
            $this->createPricedAiPriceMapTransfer(),
            'anthropic',
            'claude-haiku-4-5',
            1000,
            500,
        );

        // Assert
        // AiInteractionCostFormatter::UNPRICED_LABEL is protected; the literal is kept intentionally.
        $this->assertSame('N/A', $formattedCost);
    }

    public function testFormatRowCostReturnsUnpricedLabelWhenModelUnpricedEvenWithCurrency(): void
    {
        // Arrange
        // Build a price map whose only entry is priced and carries a currency, then format a different (unknown) model.
        $aiPriceMapTransfer = $this->createPricedAiPriceMapTransfer('USD');

        // Act
        $formattedCost = $this->createAiInteractionCostFormatter()->formatRowCost(
            $aiPriceMapTransfer,
            'anthropic',
            'claude-haiku-4-5',
            1000,
            500,
        );

        // Assert
        // The unpriced label wins regardless of any currency configured on other models.
        // Note: aggregate cost formatting is done in Twig (number_format), not in this class; formatRowCost is the only public method.
        $this->assertSame('N/A', $formattedCost);
    }
}
