<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Dependency\Tools;

use Spryker\Shared\AiFoundation\Tools\ToolParameterType;

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
     * - Returns the parameter type.
     *
     * @api
     */
    public function getType(): ToolParameterType;

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
