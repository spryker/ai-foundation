<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

interface NeuronAiToolMapperInterface
{
    /**
     * Maps an array of Spryker ToolPluginInterface instances to Neuron AI ToolInterface array.
     *
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface> $tools
     *
     * @return array<\NeuronAI\Tools\ToolInterface>
     */
    public function mapToolsToNeuronTools(array $tools): array;
}
