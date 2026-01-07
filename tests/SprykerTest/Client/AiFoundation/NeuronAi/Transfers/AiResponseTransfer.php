<?php

// phpcs:ignoreFile

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AiFoundation\NeuronAi\Transfers;

use ArrayObject;
use InvalidArgumentException;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

/**
 * !!! THIS FILE IS AUTO-GENERATED, EVERY CHANGE WILL BE LOST WITH THE NEXT RUN OF TRANSFER GENERATOR
 * !!! DO NOT CHANGE ANYTHING IN THIS FILE
 */
class AiResponseTransfer extends AbstractTransfer
{
    /**
     * @var string
     */
    public const RAND_STRING = 'randString';

    /**
     * @var string
     */
    public const ANY_OBJECT = 'anyObject';

    /**
     * @var string
     */
    public const ARRAY_OF_STRINGS = 'arrayOfStrings';

    /**
     * @var string
     */
    public const AI_RESPONSE_PATHS = 'aiResponsePaths';

    /**
     * @var string|null
     */
    protected $randString;

    /**
     * @var \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer|null
     */
    protected $anyObject;

    /**
     * @var array
     */
    protected $arrayOfStrings = [];

    /**
     * @var \ArrayObject<\SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer>
     */
    protected $aiResponsePaths;

    /**
     * @var array<string, string>
     */
    protected $transferPropertyNameMap = [
        'rand_string' => 'randString',
        'randString' => 'randString',
        'RandString' => 'randString',
        'any_object' => 'anyObject',
        'anyObject' => 'anyObject',
        'AnyObject' => 'anyObject',
        'array_of_strings' => 'arrayOfStrings',
        'arrayOfStrings' => 'arrayOfStrings',
        'ArrayOfStrings' => 'arrayOfStrings',
        'ai_response_paths' => 'aiResponsePaths',
        'aiResponsePaths' => 'aiResponsePaths',
        'AiResponsePaths' => 'aiResponsePaths',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected $transferMetadata = [
        self::RAND_STRING => [
            'type' => 'string',
            'type_shim' => null,
            'name_underscore' => 'rand_string',
            'is_collection' => false,
            'is_transfer' => false,
            'is_value_object' => false,
            'rest_request_parameter' => 'no',
            'rest_response_parameter' => 'yes',
            'example' => '',
            'description' => 'Random string',
            'is_associative' => false,
            'is_nullable' => false,
            'is_strict' => true,
            'is_primitive_array' => false,
        ],
        self::ANY_OBJECT => [
            'type' => 'SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer',
            'type_shim' => null,
            'name_underscore' => 'any_object',
            'is_collection' => false,
            'is_transfer' => true,
            'is_value_object' => false,
            'rest_request_parameter' => 'no',
            'rest_response_parameter' => 'yes',
            'example' => '',
            'description' => 'Nested transfer object for branch information',
            'is_associative' => false,
            'is_nullable' => false,
            'is_strict' => true,
            'is_primitive_array' => false,
        ],
        self::ARRAY_OF_STRINGS => [
            'type' => 'array',
            'type_shim' => null,
            'name_underscore' => 'array_of_strings',
            'is_collection' => false,
            'is_transfer' => false,
            'is_value_object' => false,
            'rest_request_parameter' => 'no',
            'rest_response_parameter' => 'yes',
            'example' => '',
            'description' => 'Array of strings',
            'is_associative' => false,
            'is_nullable' => false,
            'is_strict' => true,
            'is_primitive_array' => true,
        ],
        self::AI_RESPONSE_PATHS => [
            'type' => 'SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer',
            'type_shim' => null,
            'name_underscore' => 'ai_response_paths',
            'is_collection' => true,
            'is_transfer' => true,
            'is_value_object' => false,
            'rest_request_parameter' => 'no',
            'rest_response_parameter' => 'yes',
            'example' => '',
            'description' => 'Array of paths',
            'is_associative' => false,
            'is_nullable' => false,
            'is_strict' => true,
            'is_primitive_array' => false,
        ],
    ];

    /**
     * @module AiFoundation
     *
     * @param string|null $randString
     *
     * @return $this
     */
    public function setRandString(?string $randString = null)
    {
        $this->randString = $randString;
        $this->modifiedProperties[static::RAND_STRING] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return string|null
     */
    public function getRandString(): ?string
    {
        return $this->randString;
    }

    /**
     * @module AiFoundation
     *
     * @param string $randString
     *
     * @return $this
     */
    public function setRandStringOrFail(string $randString)
    {
        return $this->setRandString($randString);
    }

    /**
     * @module AiFoundation
     *
     * @return string
     */
    public function getRandStringOrFail(): string
    {
        if ($this->randString === null) {
            $this->throwNullValueException(static::RAND_STRING);
        }

        return $this->randString;
    }

    /**
     * @module AiFoundation
     *
     * @return $this
     */
    public function requireRandString()
    {
        $this->assertPropertyIsSet(static::RAND_STRING);

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @param \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer|null $anyObject
     *
     * @return $this
     */
    public function setAnyObject(?AiResponseBranchInfoTransfer $anyObject = null)
    {
        $this->anyObject = $anyObject;
        $this->modifiedProperties[static::ANY_OBJECT] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer|null
     */
    public function getAnyObject(): ?AiResponseBranchInfoTransfer
    {
        return $this->anyObject;
    }

    /**
     * @module AiFoundation
     *
     * @param \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer $anyObject
     *
     * @return $this
     */
    public function setAnyObjectOrFail(AiResponseBranchInfoTransfer $anyObject)
    {
        return $this->setAnyObject($anyObject);
    }

    /**
     * @module AiFoundation
     *
     * @return \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer
     */
    public function getAnyObjectOrFail(): AiResponseBranchInfoTransfer
    {
        if ($this->anyObject === null) {
            $this->throwNullValueException(static::ANY_OBJECT);
        }

        return $this->anyObject;
    }

    /**
     * @module AiFoundation
     *
     * @return $this
     */
    public function requireAnyObject()
    {
        $this->assertPropertyIsSet(static::ANY_OBJECT);

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @param array|null $arrayOfStrings
     *
     * @return $this
     */
    public function setArrayOfStrings(?array $arrayOfStrings = null)
    {
        if ($arrayOfStrings === null) {
            $arrayOfStrings = [];
        }

        $this->arrayOfStrings = $arrayOfStrings;
        $this->modifiedProperties[static::ARRAY_OF_STRINGS] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return array
     */
    public function getArrayOfStrings(): array
    {
        return $this->arrayOfStrings;
    }

    /**
     * @module AiFoundation
     *
     * @param mixed $arrayOfString
     *
     * @return $this
     */
    public function addArrayOfString($arrayOfString)
    {
        $this->arrayOfStrings[] = $arrayOfString;
        $this->modifiedProperties[static::ARRAY_OF_STRINGS] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return $this
     */
    public function requireArrayOfStrings()
    {
        $this->assertPropertyIsSet(static::ARRAY_OF_STRINGS);

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @param \ArrayObject<\SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer> $aiResponsePaths
     *
     * @return $this
     */
    public function setAiResponsePaths(ArrayObject $aiResponsePaths)
    {
        $this->aiResponsePaths = new ArrayObject();

        foreach ($aiResponsePaths as $key => $value) {
            $args = [$value];

            if ($this->transferMetadata[static::AI_RESPONSE_PATHS]['is_associative']) {
                $args = [$key, $value];
            }

            $this->addAiResponsePath(...$args);
        }

        $this->modifiedProperties[static::AI_RESPONSE_PATHS] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return \ArrayObject<\SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer>
     */
    public function getAiResponsePaths(): ArrayObject
    {
        return $this->aiResponsePaths;
    }

    /**
     * @module AiFoundation
     *
     * @param \SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer $aiResponsePath
     *
     * @return $this
     */
    public function addAiResponsePath(AiResponsePathTransfer $aiResponsePath)
    {
        $this->aiResponsePaths[] = $aiResponsePath;
        $this->modifiedProperties[static::AI_RESPONSE_PATHS] = true;

        return $this;
    }

    /**
     * @module AiFoundation
     *
     * @return $this
     */
    public function requireAiResponsePaths()
    {
        $this->assertCollectionPropertyIsSet(static::AI_RESPONSE_PATHS);

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @param bool $ignoreMissingProperty
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function fromArray(array $data, $ignoreMissingProperty = false)
    {
        foreach ($data as $property => $value) {
            $normalizedPropertyName = $this->transferPropertyNameMap[$property] ?? null;

            switch ($normalizedPropertyName) {
                case 'randString':
                case 'arrayOfStrings':
                    $this->$normalizedPropertyName = $value;
                    $this->modifiedProperties[$normalizedPropertyName] = true;

                    break;
                case 'anyObject':
                    if (is_array($value)) {
                        $type = $this->transferMetadata[$normalizedPropertyName]['type'];
                        /** @var \Spryker\Shared\Kernel\Transfer\TransferInterface $value */
                        $value = (new $type())->fromArray($value, $ignoreMissingProperty);
                    }

                    if ($value !== null && $this->isPropertyStrict($normalizedPropertyName)) {
                        $this->assertInstanceOfTransfer($normalizedPropertyName, $value);
                    }
                    $this->$normalizedPropertyName = $value;
                    $this->modifiedProperties[$normalizedPropertyName] = true;

                    break;
                case 'aiResponsePaths':
                    $elementType = $this->transferMetadata[$normalizedPropertyName]['type'];
                    $this->$normalizedPropertyName = $this->processArrayObject($elementType, $value, $ignoreMissingProperty);
                    $this->modifiedProperties[$normalizedPropertyName] = true;

                    break;
                default:
                    if (!$ignoreMissingProperty) {
                        throw new InvalidArgumentException(sprintf('Missing property `%s` in `%s`', $property, static::class));
                    }
            }
        }

        return $this;
    }

    /**
     * @param bool $isRecursive
     * @param bool $camelCasedKeys
     *
     * @return array<string, mixed>
     */
    public function modifiedToArray($isRecursive = true, $camelCasedKeys = false): array
    {
        if ($isRecursive && !$camelCasedKeys) {
            return $this->modifiedToArrayRecursiveNotCamelCased();
        }
        if ($isRecursive && $camelCasedKeys) {
            return $this->modifiedToArrayRecursiveCamelCased();
        }
        if (!$isRecursive && $camelCasedKeys) {
            return $this->modifiedToArrayNotRecursiveCamelCased();
        }
        if (!$isRecursive && !$camelCasedKeys) {
            return $this->modifiedToArrayNotRecursiveNotCamelCased();
        }
    }

    /**
     * @param bool $isRecursive
     * @param bool $camelCasedKeys
     *
     * @return array<string, mixed>
     */
    public function toArray($isRecursive = true, $camelCasedKeys = false): array
    {
        if ($isRecursive && !$camelCasedKeys) {
            return $this->toArrayRecursiveNotCamelCased();
        }
        if ($isRecursive && $camelCasedKeys) {
            return $this->toArrayRecursiveCamelCased();
        }
        if (!$isRecursive && !$camelCasedKeys) {
            return $this->toArrayNotRecursiveNotCamelCased();
        }
        if (!$isRecursive && $camelCasedKeys) {
            return $this->toArrayNotRecursiveCamelCased();
        }
    }

    /**
     * @param \ArrayObject<string, mixed>|array<string, mixed> $value
     * @param bool $isRecursive
     * @param bool $camelCasedKeys
     *
     * @return array<string, mixed>
     */
    protected function addValuesToCollectionModified($value, $isRecursive, $camelCasedKeys): array
    {
        $result = [];
        foreach ($value as $elementKey => $arrayElement) {
            if ($arrayElement instanceof AbstractTransfer) {
                $result[$elementKey] = $arrayElement->modifiedToArray($isRecursive, $camelCasedKeys);

                continue;
            }
            $result[$elementKey] = $arrayElement;
        }

        return $result;
    }

    /**
     * @param \ArrayObject<string, mixed>|array<string, mixed> $value
     * @param bool $isRecursive
     * @param bool $camelCasedKeys
     *
     * @return array<string, mixed>
     */
    protected function addValuesToCollection($value, $isRecursive, $camelCasedKeys): array
    {
        $result = [];
        foreach ($value as $elementKey => $arrayElement) {
            if ($arrayElement instanceof AbstractTransfer) {
                $result[$elementKey] = $arrayElement->toArray($isRecursive, $camelCasedKeys);

                continue;
            }
            $result[$elementKey] = $arrayElement;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function modifiedToArrayRecursiveCamelCased(): array
    {
        $values = [];
        foreach ($this->modifiedProperties as $property => $_) {
            $value = $this->$property;

            $arrayKey = $property;

            if ($value instanceof AbstractTransfer) {
                $values[$arrayKey] = $value->modifiedToArray(true, true);

                continue;
            }
            switch ($property) {
                case 'randString':
                case 'arrayOfStrings':
                    $values[$arrayKey] = $value;

                    break;
                case 'anyObject':
                    $values[$arrayKey] = $value instanceof AbstractTransfer ? $value->modifiedToArray(true, true) : $value;

                    break;
                case 'aiResponsePaths':
                    $values[$arrayKey] = $value ? $this->addValuesToCollectionModified($value, true, true) : $value;

                    break;
            }
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    public function modifiedToArrayRecursiveNotCamelCased(): array
    {
        $values = [];
        foreach ($this->modifiedProperties as $property => $_) {
            $value = $this->$property;

            $arrayKey = $this->transferMetadata[$property]['name_underscore'];

            if ($value instanceof AbstractTransfer) {
                $values[$arrayKey] = $value->modifiedToArray(true, false);

                continue;
            }
            switch ($property) {
                case 'randString':
                case 'arrayOfStrings':
                    $values[$arrayKey] = $value;

                    break;
                case 'anyObject':
                    $values[$arrayKey] = $value instanceof AbstractTransfer ? $value->modifiedToArray(true, false) : $value;

                    break;
                case 'aiResponsePaths':
                    $values[$arrayKey] = $value ? $this->addValuesToCollectionModified($value, true, false) : $value;

                    break;
            }
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    public function modifiedToArrayNotRecursiveNotCamelCased(): array
    {
        $values = [];
        foreach ($this->modifiedProperties as $property => $_) {
            $value = $this->$property;

            $arrayKey = $this->transferMetadata[$property]['name_underscore'];

            $values[$arrayKey] = $value;
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    public function modifiedToArrayNotRecursiveCamelCased(): array
    {
        $values = [];
        foreach ($this->modifiedProperties as $property => $_) {
            $value = $this->$property;

            $arrayKey = $property;

            $values[$arrayKey] = $value;
        }

        return $values;
    }

    /**
     * @return void
     */
    protected function initCollectionProperties(): void
    {
        $this->aiResponsePaths = $this->aiResponsePaths ?: new ArrayObject();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayNotRecursiveCamelCased(): array
    {
        return [
            'randString' => $this->randString,
            'arrayOfStrings' => $this->arrayOfStrings,
            'anyObject' => $this->anyObject,
            'aiResponsePaths' => $this->aiResponsePaths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayNotRecursiveNotCamelCased(): array
    {
        return [
            'rand_string' => $this->randString,
            'array_of_strings' => $this->arrayOfStrings,
            'any_object' => $this->anyObject,
            'ai_response_paths' => $this->aiResponsePaths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayRecursiveNotCamelCased(): array
    {
        return [
            'rand_string' => $this->randString instanceof AbstractTransfer ? $this->randString->toArray(true, false) : $this->randString,
            'array_of_strings' => $this->arrayOfStrings instanceof AbstractTransfer ? $this->arrayOfStrings->toArray(true, false) : $this->arrayOfStrings,
            'any_object' => $this->anyObject instanceof AbstractTransfer ? $this->anyObject->toArray(true, false) : $this->anyObject,
            'ai_response_paths' => $this->aiResponsePaths instanceof AbstractTransfer ? $this->aiResponsePaths->toArray(true, false) : $this->addValuesToCollection($this->aiResponsePaths, true, false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayRecursiveCamelCased(): array
    {
        return [
            'randString' => $this->randString instanceof AbstractTransfer ? $this->randString->toArray(true, true) : $this->randString,
            'arrayOfStrings' => $this->arrayOfStrings instanceof AbstractTransfer ? $this->arrayOfStrings->toArray(true, true) : $this->arrayOfStrings,
            'anyObject' => $this->anyObject instanceof AbstractTransfer ? $this->anyObject->toArray(true, true) : $this->anyObject,
            'aiResponsePaths' => $this->aiResponsePaths instanceof AbstractTransfer ? $this->aiResponsePaths->toArray(true, true) : $this->addValuesToCollection($this->aiResponsePaths, true, true),
        ];
    }
}
