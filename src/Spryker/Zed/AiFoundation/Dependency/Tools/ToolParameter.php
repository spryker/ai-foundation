<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Dependency\Tools;

class ToolParameter implements ToolParameterInterface
{
    /**
     * @param 'string'|'integer'|'number'|'boolean'|'array'|'object' $type
     */
    public function __construct(
        protected string $name,
        protected string $type,
        protected string $description,
        protected bool $isRequired = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return 'string'|'integer'|'number'|'boolean'|'array'|'object'
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }
}
