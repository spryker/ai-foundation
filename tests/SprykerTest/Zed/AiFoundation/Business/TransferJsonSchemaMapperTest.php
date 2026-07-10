<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business;

use Codeception\Test\Unit;
use InvalidArgumentException;
use ReflectionClass;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;
use SprykerTest\Zed\AiFoundation\Business\NeuronAi\Transfers\AiResponseTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group TransferJsonSchemaMapperTest
 * Add your own group annotations below this line
 */
class TransferJsonSchemaMapperTest extends Unit
{
    protected AiFoundationBusinessTester $tester;

    public function testGivenTransferWithMissingPropertyDescriptionWhenBuildingJsonSchemaThenExceptionIsThrown(): void
    {
        // Arrange
        $transferJsonSchemaMapper = new TransferJsonSchemaMapper();
        $transferWithMissingDescription = $this->createTransferWithMissingDescription();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Property "test_property" .* is missing a required description for structured AI responses/');

        // Act
        $transferJsonSchemaMapper->buildJsonSchema($transferWithMissingDescription);
    }

    /**
     * @dataProvider extractJsonFromTextDataProvider
     *
     * @param array<mixed> $expected
     */
    public function testGivenTextInputWhenExtractingJsonThenResultIsAlwaysArray(string $input, array $expected): void
    {
        // Arrange
        $transferJsonSchemaMapper = new TransferJsonSchemaMapper();

        // Act
        $result = $transferJsonSchemaMapper->extractJsonFromText($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{0: string, 1: array<mixed>}>
     */
    public function extractJsonFromTextDataProvider(): array
    {
        return [
            'bare scalar integer string (regression CC-39610)' => ['1', []],
            'bare boolean literal' => ['true', []],
            'bare float literal' => ['3.14', []],
            'bare null literal' => ['null', []],
            'invalid json plain text' => ['hello', []],
            'json object' => ['{"a":1}', ['a' => 1]],
            'json array' => ['[1,2,3]', [1, 2, 3]],
            'fenced json code block' => ["```json\n{\"x\": 10}\n```", ['x' => 10]],
            'object embedded in prose' => ['Here you go: {"y":5} thanks', ['y' => 5]],
        ];
    }

    protected function createTransferWithMissingDescription(): AbstractTransfer
    {
        $transfer = new AiResponseTransfer();

        $reflectionClass = new ReflectionClass($transfer);
        $transferMetadataProperty = $reflectionClass->getProperty('transferMetadata');
        $transferMetadataProperty->setAccessible(true);

        $metadata = [
            'test_property' => [
                'type' => 'string',
                'name_underscore' => 'test_property',
                'is_collection' => false,
                'is_transfer' => false,
                'is_primitive_array' => false,
                'description' => '',
            ],
        ];

        $transferMetadataProperty->setValue($transfer, $metadata);

        return $transfer;
    }
}
