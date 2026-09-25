<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Dependency\Tools;

/**
 * Defines the contract for AI tool parameters.
 */
interface ToolParameterInterface
{
    /**
     * Specification:
     * - Returns the parameter name.
     *
     * @api
     */
    public function getName(): string;

    /**
     * Specification:
     * - Returns the parameter type as one of the `\Spryker\Shared\AiFoundation\Tools\ToolParameterType` values.
     * - Falls back to `ToolParameterType::String` when the returned value is not a `ToolParameterType` value.
     *
     * @api
     */
    public function getType(): string;

    /**
     * Specification:
     * - Returns a description of what this parameter is used for.
     *
     * @api
     */
    public function getDescription(): string;

    /**
     * Specification:
     * - Returns whether this parameter is required.
     *
     * @api
     */
    public function isRequired(): bool;
}
