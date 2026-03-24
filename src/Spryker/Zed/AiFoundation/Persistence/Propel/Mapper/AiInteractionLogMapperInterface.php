<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionTransfer;
use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Maps AiInteractionLog data between Transfer Object and Propel Entity.
 */
interface AiInteractionLogMapperInterface
{
    /**
     * Maps an AiInteractionLog transfer object to a Propel entity.
     */
    public function mapAiInteractionLogTransferToEntity(
        AiInteractionLogTransfer $aiInteractionLogTransfer,
        SpyAiInteractionLog $aiInteractionLogEntity,
    ): SpyAiInteractionLog;

    /**
     * Maps a Propel entity to an AiInteractionLog transfer object.
     */
    public function mapAiInteractionLogEntityToTransfer(
        SpyAiInteractionLog $aiInteractionLogEntity,
        AiInteractionLogTransfer $aiInteractionLogTransfer,
    ): AiInteractionLogTransfer;

    /**
     * Maps a collection of Propel entities to an AiInteractionLogCollection transfer.
     *
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog> $aiInteractionLogEntities
     */
    public function mapAiInteractionLogEntityCollectionToCollectionTransfer(
        ObjectCollection $aiInteractionLogEntities,
        AiInteractionLogCollectionTransfer $aiInteractionLogCollectionTransfer,
    ): AiInteractionLogCollectionTransfer;

    /**
     * Maps aggregation query result row to AiInteractionLogAggregation transfer.
     *
     * @param array<string, mixed> $row
     */
    public function mapAggregationRowToAiInteractionLogAggregationTransfer(
        array $row,
        AiInteractionLogAggregationTransfer $aiInteractionLogAggregationTransfer,
    ): AiInteractionLogAggregationTransfer;
}
