<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiModelPriceTransfer;
use Spryker\Service\AiFoundation\AiFoundationService;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost\AiInteractionCostCalculator;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiInteractionLog
 * @group Cost
 * @group AiInteractionCostCalculatorTest
 * Add your own group annotations below this line
 */
class AiInteractionCostCalculatorTest extends Unit
{
    protected function createAiInteractionCostCalculator(): AiInteractionCostCalculator
    {
        return new AiInteractionCostCalculator(new AiFoundationService(), new AiFoundationConfig());
    }

    public function testCalculateRowCostReturnsRoundedCostForPricedModel(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())
            ->setInputPricePerMillion(2.5)
            ->setOutputPricePerMillion(10.0)
            ->setIsPriced(true);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateRowCost(1000, 500, $aiModelPriceTransfer);

        // Assert
        // (1000 / 1_000_000 * 2.5) + (500 / 1_000_000 * 10.0) = 0.0025 + 0.005 = 0.0075
        $this->assertSame(0.0075, $cost);
    }

    public function testCalculateRowCostReturnsNullForUnpricedModel(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())->setIsPriced(false);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateRowCost(1000, 500, $aiModelPriceTransfer);

        // Assert
        $this->assertNull($cost);
    }

    public function testCalculateBreakdownCostRoundsToTwoDecimals(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())
            ->setInputPricePerMillion(3.0)
            ->setOutputPricePerMillion(15.0)
            ->setIsPriced(true);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateBreakdownCost(1000000, 1000000, $aiModelPriceTransfer);

        // Assert
        // (1_000_000 / 1_000_000 * 3.0) + (1_000_000 / 1_000_000 * 15.0) = 18.0
        $this->assertSame(18.0, $cost);
    }

    public function testCalculateBreakdownCostWithZeroTokensReturnsZero(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())
            ->setInputPricePerMillion(3.0)
            ->setOutputPricePerMillion(15.0)
            ->setIsPriced(true);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateBreakdownCost(0, 0, $aiModelPriceTransfer);

        // Assert
        // A priced model with zero tokens costs 0.0, not null (null is reserved for unpriced models).
        $this->assertSame(0.0, $cost);
    }

    public function testCalculateBreakdownCostRoundsFractionalToTwoDecimals(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())
            ->setInputPricePerMillion(3.0)
            ->setOutputPricePerMillion(0.0)
            ->setIsPriced(true);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateBreakdownCost(41150, 0, $aiModelPriceTransfer);

        // Assert
        // 41150 / 1_000_000 * 3.0 = 0.12345 → rounded to the aggregate precision (2) = 0.12.
        $this->assertSame(0.12, $cost);
    }

    public function testCalculateBreakdownCostReturnsNullForUnpricedModel(): void
    {
        // Arrange
        $aiModelPriceTransfer = (new AiModelPriceTransfer())->setIsPriced(false);

        // Act
        $cost = $this->createAiInteractionCostCalculator()->calculateBreakdownCost(123, 456, $aiModelPriceTransfer);

        // Assert
        $this->assertNull($cost);
    }
}
