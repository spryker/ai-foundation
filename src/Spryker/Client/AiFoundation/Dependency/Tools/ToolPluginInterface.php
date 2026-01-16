<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Dependency\Tools;

/**
 * Defines the contract for AI tools that can be invoked by large language models.
 */
interface ToolPluginInterface
{
    /**
     * Specification:
     * - Returns the unique identifier for this tool.
     *
     * @api
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Specification:
     * - Returns a human-readable description of what this tool does.
     * - This description is used by the large language model to understand when to use this tool.
     *
     * @api
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Specification:
     * - Returns the list of parameters this tool accepts.
     *
     * @api
     *
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolParameterInterface>
     */
    public function getParameters(): array;

    /**
     * Specification:
     * - Executes the tool with the provided arguments.
     *
     * @api
     *
     * @param mixed ...$arguments Variable number of arguments passed to the tool
     *
     * @return mixed
     */
    public function execute(...$arguments): mixed;
}
