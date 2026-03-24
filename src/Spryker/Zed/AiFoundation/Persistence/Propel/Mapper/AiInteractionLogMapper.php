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

class AiInteractionLogMapper implements AiInteractionLogMapperInterface
{
    public const string VIRTUAL_COLUMN_TOTAL_REQUESTS = 'totalRequests';

    public const string VIRTUAL_COLUMN_TOTAL_TOKENS = 'totalTokens';

    public const string VIRTUAL_COLUMN_SUCCESS_COUNT = 'successCount';

    public const string VIRTUAL_COLUMN_AVERAGE_INFERENCE_TIME_MS = 'averageInferenceTimeMs';

    public function mapAiInteractionLogTransferToEntity(
        AiInteractionLogTransfer $aiInteractionLogTransfer,
        SpyAiInteractionLog $aiInteractionLogEntity,
    ): SpyAiInteractionLog {
        $aiInteractionLogEntity->fromArray($aiInteractionLogTransfer->toArray());

        return $aiInteractionLogEntity;
    }

    public function mapAiInteractionLogEntityToTransfer(
        SpyAiInteractionLog $aiInteractionLogEntity,
        AiInteractionLogTransfer $aiInteractionLogTransfer,
    ): AiInteractionLogTransfer {
        $aiInteractionLogTransfer->fromArray($aiInteractionLogEntity->toArray(), true);

        return $aiInteractionLogTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog> $aiInteractionLogEntities
     */
    public function mapAiInteractionLogEntityCollectionToCollectionTransfer(
        ObjectCollection $aiInteractionLogEntities,
        AiInteractionLogCollectionTransfer $aiInteractionLogCollectionTransfer,
    ): AiInteractionLogCollectionTransfer {
        foreach ($aiInteractionLogEntities as $aiInteractionLogEntity) {
            $aiInteractionLogCollectionTransfer->addAiInteractionLog(
                $this->mapAiInteractionLogEntityToTransfer(
                    $aiInteractionLogEntity,
                    new AiInteractionLogTransfer(),
                ),
            );
        }

        return $aiInteractionLogCollectionTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $row
     */
    public function mapAggregationRowToAiInteractionLogAggregationTransfer(
        array $row,
        AiInteractionLogAggregationTransfer $aiInteractionLogAggregationTransfer,
    ): AiInteractionLogAggregationTransfer {
        $aiInteractionLogAggregationTransfer
            ->setTotalRequests((int)($row[static::VIRTUAL_COLUMN_TOTAL_REQUESTS] ?? 0))
            ->setTotalTokens((int)($row[static::VIRTUAL_COLUMN_TOTAL_TOKENS] ?? 0))
            ->setSuccessCount((int)($row[static::VIRTUAL_COLUMN_SUCCESS_COUNT] ?? 0))
            ->setAverageInferenceTimeMs((float)($row[static::VIRTUAL_COLUMN_AVERAGE_INFERENCE_TIME_MS] ?? 0));

        return $aiInteractionLogAggregationTransfer;
    }
}
