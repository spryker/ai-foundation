<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AiFoundation;

use Codeception\Test\Unit;
use InvalidArgumentException;
use ReflectionClass;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapper;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group TransferJsonSchemaMapperTest
 * Add your own group annotations below this line
 */
class TransferJsonSchemaMapperTest extends Unit
{
    protected AiFoundationClientTester $tester;

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
