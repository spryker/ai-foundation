<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Dependency\Tools;

/**
 * Defines the contract for AI tool sets that group multiple related AI tools.
 */
interface ToolSetPluginInterface
{
    /**
     * Specification:
     * - Returns the unique identifier for this tool set.
     *
     * @api
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Specification:
     * - Returns the list of AI tools included in this tool set.
     *
     * @api
     *
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface>
     */
    public function getTools(): array;
}
