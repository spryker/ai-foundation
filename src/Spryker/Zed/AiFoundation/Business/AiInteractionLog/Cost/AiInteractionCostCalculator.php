<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Cost;

use Generated\Shared\Transfer\AiModelPriceTransfer;
use Spryker\Service\AiFoundation\AiFoundationServiceInterface;
use Spryker\Zed\AiFoundation\AiFoundationConfig;

class AiInteractionCostCalculator implements AiInteractionCostCalculatorInterface
{
    public function __construct(
        protected AiFoundationServiceInterface $aiFoundationService,
        protected AiFoundationConfig $aiFoundationConfig,
    ) {
    }

    public function calculateRowCost(int $inputTokens, int $outputTokens, AiModelPriceTransfer $aiModelPriceTransfer): ?float
    {
        return $this->aiFoundationService->calculateAiInteractionCost(
            $aiModelPriceTransfer,
            $inputTokens,
            $outputTokens,
            $this->aiFoundationConfig->getTokensPerPriceUnit(),
            $this->aiFoundationConfig->getCostRoundingPrecisionForRow(),
        );
    }

    public function calculateBreakdownCost(int $inputTokens, int $outputTokens, AiModelPriceTransfer $aiModelPriceTransfer): ?float
    {
        return $this->aiFoundationService->calculateAiInteractionCost(
            $aiModelPriceTransfer,
            $inputTokens,
            $outputTokens,
            $this->aiFoundationConfig->getTokensPerPriceUnit(),
            $this->aiFoundationConfig->getCostRoundingPrecisionForAggregate(),
        );
    }
}
