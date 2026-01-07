<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Mapper;

use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

interface TransferJsonSchemaMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildJsonSchema(AbstractTransfer $transfer): array;

    /**
     * @return array<string, mixed>
     */
    public function extractJsonFromText(string $input): array;
}
