<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolProperty;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolParameterInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface;

class NeuronAiToolMapper implements NeuronAiToolMapperInterface
{
    protected const string TYPE_STRING = 'string';

    protected const string TYPE_INTEGER = 'integer';

    protected const string TYPE_NUMBER = 'number';

    protected const string TYPE_BOOLEAN = 'boolean';

    protected const string TYPE_ARRAY = 'array';

    protected const string TYPE_OBJECT = 'object';

    protected const array PARAMETER_TYPE_MAP = [
        self::TYPE_INTEGER => PropertyType::INTEGER,
        self::TYPE_STRING => PropertyType::STRING,
        self::TYPE_NUMBER => PropertyType::NUMBER,
        self::TYPE_BOOLEAN => PropertyType::BOOLEAN,
        self::TYPE_ARRAY => PropertyType::ARRAY,
        self::TYPE_OBJECT => PropertyType::OBJECT,
    ];

    /**
     * @inheritDoc
     */
    public function mapToolsToNeuronTools(array $tools): array
    {
        $neuronTools = [];

        foreach ($tools as $tool) {
            $neuronTools[] = $this->mapToolToNeuronTool($tool);
        }

        return $neuronTools;
    }

    /**
     * @param \Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface $tool
     *
     * @return \NeuronAI\Tools\ToolInterface
     */
    protected function mapToolToNeuronTool(ToolPluginInterface $tool): ToolInterface
    {
        $neuronTool = new Tool(
            name: $tool->getName(),
            description: $tool->getDescription(),
        );

        foreach ($tool->getParameters() as $parameter) {
            $property = $this->mapParameterToToolProperty($parameter);
            $neuronTool->addProperty($property);
        }

        $neuronTool->setCallable(function (...$arguments) use ($tool) {
            return $tool->execute(...$arguments);
        });

        return $neuronTool;
    }

    /**
     * @param \Spryker\Client\AiFoundation\Dependency\Tools\ToolParameterInterface $parameter
     *
     * @return \NeuronAI\Tools\ToolProperty
     */
    protected function mapParameterToToolProperty(ToolParameterInterface $parameter): ToolProperty
    {
        return new ToolProperty(
            name: $parameter->getName(),
            type: static::PARAMETER_TYPE_MAP[$parameter->getType()] ?? PropertyType::STRING,
            description: $parameter->getDescription(),
            required: $parameter->isRequired(),
        );
    }
}
