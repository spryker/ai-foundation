<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog;

/**
 * Maps AiInteractionLog data between Transfer Object and Propel Entity.
 */
interface AiInteractionLogMapperInterface
{
    public function mapAiInteractionLogTransferToEntity(
        AiInteractionLogTransfer $aiInteractionLogTransfer,
        SpyAiInteractionLog $aiInteractionLogEntity,
    ): SpyAiInteractionLog;
}
