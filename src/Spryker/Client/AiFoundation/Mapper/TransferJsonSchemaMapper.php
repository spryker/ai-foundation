<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Mapper;

use InvalidArgumentException;
use JsonException;
use ReflectionClass;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class TransferJsonSchemaMapper implements TransferJsonSchemaMapperInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer
     *
     * @return array<string, mixed>
     */
    public function buildJsonSchema(AbstractTransfer $transfer): array
    {
        $reflectionClass = new ReflectionClass($transfer);
        $transferMetadataProperty = $reflectionClass->getProperty('transferMetadata');
        $transferMetadataProperty->setAccessible(true);
        $metadata = $transferMetadataProperty->getValue($transfer);

        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        $schema['additionalProperties'] = false;

        $transferClassName = get_class($transfer);

        foreach ($metadata as $propertyMetadata) {
            $propertyName = $propertyMetadata['name_underscore'];
            $propertySchema = $this->buildPropertyJsonSchema($propertyMetadata, $transferClassName);

            $schema['properties'][$propertyName] = $propertySchema;
            $schema['required'][] = $propertyName;
        }

        return $schema;
    }

    /**
     * @param array<string, mixed> $propertyMetadata
     * @param string $transferClassName
     *
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    protected function buildPropertyJsonSchema(array $propertyMetadata, string $transferClassName): array
    {
        $schema = [];

        if (empty($propertyMetadata['description'])) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" in transfer "%s" is missing a required description for structured AI responses.',
                $propertyMetadata['name_underscore'],
                $transferClassName,
            ));
        }

        $schema['description'] = $propertyMetadata['description'];

        if ($propertyMetadata['is_collection'] && $propertyMetadata['is_transfer']) {
            $transferClass = $propertyMetadata['type'];
            $nestedTransfer = new $transferClass();
            assert($nestedTransfer instanceof AbstractTransfer);
            $nestedSchema = $this->buildJsonSchema($nestedTransfer);

            $schema['type'] = 'array';
            $schema['items'] = $nestedSchema;

            return $schema;
        }

        if ($propertyMetadata['is_transfer']) {
            $transferClass = $propertyMetadata['type'];
            $nestedTransfer = new $transferClass();
            assert($nestedTransfer instanceof AbstractTransfer);
            $nestedSchema = $this->buildJsonSchema($nestedTransfer);

            return array_merge($schema, $nestedSchema);
        }

        if ($propertyMetadata['is_primitive_array']) {
            $schema['type'] = 'array';
            $schema['items'] = ['type' => 'string'];

            return $schema;
        }

        $schema['type'] = $this->mapPhpTypeToJsonSchemaType($propertyMetadata['type']);

        return $schema;
    }

    /**
     * @param string $phpType
     *
     * @return string
     */
    protected function mapPhpTypeToJsonSchemaType(string $phpType): string
    {
        return match ($phpType) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            default => 'string',
        };
    }

    public function extractJsonFromText(string $input): array
    {
        $input = trim($input);

        try {
            return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // Do nothing
        }

        if (preg_match_all('/```(?:json)?\s*(.*?)\s*```/si', $input, $blocks)) {
            foreach ($blocks[1] as $block) {
                try {
                    return json_decode(trim($block), true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    // do nothing
                }
            }
        }

        $json = $this->extractBalancedJson($input);
        if ($json !== null) {
            try {
                return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return [];
            }
        }

        return [];
    }

    protected function extractBalancedJson(string $input): ?string
    {
        $length = strlen($input);
        $stack = [];
        $start = null;
        $inString = false;
        $escape = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];

            if ($char === '"' && !$escape) {
                $inString = !$inString;
            }

            $escape = ($char === '\\' && !$escape);

            if ($inString) {
                continue;
            }

            if ($start === null && ($char === '{' || $char === '[')) {
                $start = $i;
                $stack[] = $char;

                continue;
            }

            if ($start !== null) {
                if ($char === '{' || $char === '[') {
                    $stack[] = $char;
                } elseif ($char === '}' || $char === ']') {
                    array_pop($stack);

                    if (!$stack) {
                        return substr($input, $start, $i - $start + 1);
                    }
                }
            }
        }

        return null;
    }
}
