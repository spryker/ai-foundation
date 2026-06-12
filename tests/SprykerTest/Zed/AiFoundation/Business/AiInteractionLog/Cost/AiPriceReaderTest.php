<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Codeception\Test\Unit;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost\AiPriceReader;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiInteractionLog
 * @group Cost
 * @group AiPriceReaderTest
 * Add your own group annotations below this line
 */
class AiPriceReaderTest extends Unit
{
    protected const string PROVIDER = 'openai';

    protected const string PROVIDER_TAB = 'openai';

    protected const string MODEL = 'gpt-4.1';

    /**
     * @param array<string, mixed> $modelPrices
     */
    protected function createAiPriceReader(array $modelPrices): AiPriceReader
    {
        // Copy the protected constants into locals so the anonymous class can receive them via its constructor.
        $provider = static::PROVIDER;
        $providerTab = static::PROVIDER_TAB;

        $aiFoundationConfig = new class ($modelPrices, $provider, $providerTab) extends AiFoundationConfig {
            /**
             * @param array<string, mixed> $stubModelPrices
             */
            public function __construct(
                protected array $stubModelPrices,
                protected string $provider,
                protected string $providerTab,
            ) {
            }

            public function getProviderPricingTabMap(): array
            {
                return [$this->provider => $this->providerTab];
            }

            public function getAiModelPricesForProviderTab(string $providerTab): array
            {
                return $providerTab === $this->providerTab ? $this->stubModelPrices : [];
            }
        };

        return new AiPriceReader($aiFoundationConfig);
    }

    public function testGetAiPriceMapMarksModelPricedWhenBothPricesAreValid(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 2.5, 'output' => 10.0],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertTrue($aiModelPriceTransfer->getIsPriced());
        $this->assertSame(2.5, $aiModelPriceTransfer->getInputPricePerMillion());
        $this->assertSame(10.0, $aiModelPriceTransfer->getOutputPricePerMillion());
    }

    public function testGetAiPriceMapSetsCurrencyWhenConfigured(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 2.5, 'output' => 10.0, 'currency' => 'USD'],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertSame('USD', $aiModelPriceTransfer->getCurrency());
    }

    public function testGetAiPriceMapLeavesCurrencyNullWhenNotConfigured(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 2.5, 'output' => 10.0],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertNull($aiModelPriceTransfer->getCurrency());
    }

    public function testGetAiPriceMapLeavesCurrencyNullWhenConfiguredBlank(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 2.5, 'output' => 10.0, 'currency' => '  '],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertNull($aiModelPriceTransfer->getCurrency());
    }

    public function testGetAiPriceMapMarksModelUnpricedWhenAPriceIsEmpty(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 2.5, 'output' => ''],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertFalse($aiModelPriceTransfer->getIsPriced());
    }

    public function testGetAiPriceMapMarksModelUnpricedWhenPriceIsMalformed(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => 'not-a-number', 'output' => 10.0],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertFalse($aiModelPriceTransfer->getIsPriced());
    }

    public function testGetAiPriceMapMarksModelUnpricedWhenPriceIsNegative(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([
            static::MODEL => ['input' => -2.5, 'output' => 10.0],
        ]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, static::PROVIDER, static::MODEL);

        // Assert
        $this->assertFalse($aiModelPriceTransfer->getIsPriced());
    }

    public function testResolveAiModelPriceReturnsUnpricedEntryForUnknownCombination(): void
    {
        // Arrange
        $aiPriceReader = $this->createAiPriceReader([]);

        // Act
        $aiPriceMapTransfer = $aiPriceReader->getAiPriceMap();
        $aiModelPriceTransfer = $aiPriceReader->resolveAiModelPrice($aiPriceMapTransfer, 'unknown', 'unknown-model');

        // Assert
        $this->assertFalse($aiModelPriceTransfer->getIsPriced());
        $this->assertSame('unknown', $aiModelPriceTransfer->getProvider());
        $this->assertSame('unknown-model', $aiModelPriceTransfer->getModel());
    }
}
