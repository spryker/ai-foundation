<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog;

class AiInteractionLogMapper implements AiInteractionLogMapperInterface
{
    public function mapAiInteractionLogTransferToEntity(
        AiInteractionLogTransfer $aiInteractionLogTransfer,
        SpyAiInteractionLog $aiInteractionLogEntity,
    ): SpyAiInteractionLog {
        $aiInteractionLogEntity->fromArray($aiInteractionLogTransfer->toArray());

        return $aiInteractionLogEntity;
    }
}
