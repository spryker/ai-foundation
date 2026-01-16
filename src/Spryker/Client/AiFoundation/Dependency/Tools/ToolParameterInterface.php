<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Dependency\Tools;

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
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Specification:
     * - Returns the parameter type: string, integer, number, boolean, array, object.
     *
     * @api
     *
     * @return 'string'|'integer'|'number'|'boolean'|'array'|'object'
     */
    public function getType(): string;

    /**
     * Specification:
     * - Returns a description of what this parameter is used for.
     *
     * @api
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Specification:
     * - Returns whether this parameter is required.
     *
     * @api
     *
     * @return bool
     */
    public function isRequired(): bool;
}
