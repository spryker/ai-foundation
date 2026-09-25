<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolProperty;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolParameterInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface;

class ClientNeuronAiToolMapper implements ClientNeuronAiToolMapperInterface
{
    /**
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface> $tools
     *
     * @return array<\NeuronAI\Tools\ToolInterface>
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
            $neuronTool->addProperty($this->mapParameterToToolProperty($parameter));
        }

        $neuronTool->setCallable(function (...$arguments) use ($tool) {
            return $tool->execute(...$arguments);
        });

        return $neuronTool;
    }

    protected function mapParameterToToolProperty(ToolParameterInterface $parameter): ToolProperty
    {
        return new ToolProperty(
            name: $parameter->getName(),
            type: PropertyType::from($parameter->getType()->value),
            description: $parameter->getDescription(),
            required: $parameter->isRequired(),
        );
    }
}
