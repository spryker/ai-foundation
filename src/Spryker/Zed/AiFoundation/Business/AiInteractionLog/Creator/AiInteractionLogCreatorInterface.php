<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Creator;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;

/**
 * Creates AI interaction log entries in the persistence layer.
 */
interface AiInteractionLogCreatorInterface
{
    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer,
    ): AiInteractionLogCollectionResponseTransfer;
}
