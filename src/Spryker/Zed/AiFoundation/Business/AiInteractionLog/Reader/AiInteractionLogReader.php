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
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class AiInteractionLogReader implements AiInteractionLogReaderInterface
{
    protected const int DEFAULT_RATE_VALUE = 0;

    protected const int ROUNDING_PRECISION = 2;

    public function __construct(protected AiFoundationRepositoryInterface $aiFoundationRepository)
    {
    }

    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionResponseTransfer {
        $collectionTransfer = $this->aiFoundationRepository->getAiInteractionLogCollection(
            $aiInteractionLogCriteriaTransfer,
        );

        return (new AiInteractionLogCollectionResponseTransfer())
            ->setAiInteractionLogCollection($collectionTransfer)
            ->setIsSuccessful(true);
    }

    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer {
        $aggregationTransfer = $this->aiFoundationRepository->getAiInteractionLogAggregation(
            $aiInteractionLogCriteriaTransfer,
        );

        $aggregationTransfer
            ->setSuccessRate($this->calculateSuccessRate($aggregationTransfer->getTotalRequests() ?? static::DEFAULT_RATE_VALUE, $aggregationTransfer->getSuccessCount() ?? static::DEFAULT_RATE_VALUE))
            ->setAverageInferenceTimeMs(round($aggregationTransfer->getAverageInferenceTimeMs() ?? static::DEFAULT_RATE_VALUE, static::ROUNDING_PRECISION));

        return $aggregationTransfer;
    }

    protected function calculateSuccessRate(int $totalRequests, int $successCount): float
    {
        if ($totalRequests === 0) {
            return (float)static::DEFAULT_RATE_VALUE;
        }

        return round($successCount * 100.0 / $totalRequests, static::ROUNDING_PRECISION);
    }
}
