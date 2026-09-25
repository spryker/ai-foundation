<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolProperty;
use Spryker\Shared\AiFoundation\Tools\ToolParameterType;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolParameterInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolPluginInterface;

class NeuronAiToolMapper implements NeuronAiToolMapperInterface
{
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

    protected function mapParameterToToolProperty(ToolParameterInterface $parameter): ToolProperty
    {
        $toolParameterType = ToolParameterType::tryFrom($parameter->getType()) ?? ToolParameterType::String;

        return new ToolProperty(
            name: $parameter->getName(),
            type: PropertyType::from($toolParameterType->value),
            description: $parameter->getDescription(),
            required: $parameter->isRequired(),
        );
    }
}
