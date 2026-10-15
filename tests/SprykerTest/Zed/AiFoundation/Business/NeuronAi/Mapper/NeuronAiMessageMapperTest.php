<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi\Mapper;

use Codeception\Test\Unit;
use NeuronAI\Chat\Messages\Usage;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor\MessageContentExtractor;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group NeuronAi
 * @group Mapper
 * @group NeuronAiMessageMapperTest
 * Add your own group annotations below this line
 */
class NeuronAiMessageMapperTest extends Unit
{
    protected const int FIRST_INPUT_TOKENS = 100;

    protected const int FIRST_OUTPUT_TOKENS = 10;

    protected const int SECOND_INPUT_TOKENS = 150;

    protected const int SECOND_OUTPUT_TOKENS = 20;

    protected AiFoundationBusinessTester $tester;

    public function testGivenSeveralUsagesWhenMappingUsagesThenTokensAreSummed(): void
    {
        // Arrange
        $usages = [
            new Usage(static::FIRST_INPUT_TOKENS, static::FIRST_OUTPUT_TOKENS),
            new Usage(static::SECOND_INPUT_TOKENS, static::SECOND_OUTPUT_TOKENS),
        ];

        // Act
        $usageTransfer = $this->createNeuronAiMessageMapper()->mapUsagesToUsageTransfer($usages);

        // Assert
        $this->assertNotNull($usageTransfer);
        $this->assertSame(static::FIRST_INPUT_TOKENS + static::SECOND_INPUT_TOKENS, $usageTransfer->getInputTokens());
        $this->assertSame(static::FIRST_OUTPUT_TOKENS + static::SECOND_OUTPUT_TOKENS, $usageTransfer->getOutputTokens());
    }

    public function testGivenUsagesWithNullWhenMappingUsagesThenNullIsIgnored(): void
    {
        // Arrange
        $usages = [null, new Usage(static::FIRST_INPUT_TOKENS, static::FIRST_OUTPUT_TOKENS), null];

        // Act
        $usageTransfer = $this->createNeuronAiMessageMapper()->mapUsagesToUsageTransfer($usages);

        // Assert
        $this->assertNotNull($usageTransfer);
        $this->assertSame(static::FIRST_INPUT_TOKENS, $usageTransfer->getInputTokens());
        $this->assertSame(static::FIRST_OUTPUT_TOKENS, $usageTransfer->getOutputTokens());
    }

    /**
     * @dataProvider noUsageDataProvider
     *
     * @param array<\NeuronAI\Chat\Messages\Usage|null> $usages
     */
    public function testGivenNoUsageWhenMappingUsagesThenNullIsReturned(array $usages): void
    {
        // Act
        $usageTransfer = $this->createNeuronAiMessageMapper()->mapUsagesToUsageTransfer($usages);

        // Assert
        $this->assertNull($usageTransfer);
    }

    /**
     * @return array<string, array<array<null>>>
     */
    public static function noUsageDataProvider(): array
    {
        return [
            'empty list' => [[]],
            'only nulls' => [[null, null]],
        ];
    }

    protected function createNeuronAiMessageMapper(): NeuronAiMessageMapper
    {
        return new NeuronAiMessageMapper(new TransferJsonSchemaMapper(), new MessageContentExtractor());
    }
}
