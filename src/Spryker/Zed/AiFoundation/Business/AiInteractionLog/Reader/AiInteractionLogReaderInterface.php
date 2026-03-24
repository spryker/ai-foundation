<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Reader;

use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;

/**
 * Reads AI interaction logs from persistence.
 */
interface AiInteractionLogReaderInterface
{
    /**
     * Retrieves AI interaction log collection filtered by criteria.
     */
    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionResponseTransfer;

    /**
     * Returns aggregated statistics for AI interaction logs filtered by criteria.
     */
    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer;
}
