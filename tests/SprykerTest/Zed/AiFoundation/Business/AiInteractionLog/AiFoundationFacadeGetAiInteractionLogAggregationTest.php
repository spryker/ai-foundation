<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiInteractionLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiInteractionLogConditionsTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiInteractionLog
 * @group Facade
 * @group AiFoundationFacadeGetAiInteractionLogAggregationTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeGetAiInteractionLogAggregationTest extends Unit
{
    protected AiFoundationBusinessTester $tester;

    public function testGivenNoMatchingDataWhenGettingAggregationThenReturnsZeroValues(): void
    {
        // Arrange
        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName(sprintf('nonexistent_%s', uniqid())),
            );

        // Act
        $aggregationTransfer = $this->tester->getFacade()->getAiInteractionLogAggregation($criteriaTransfer);

        // Assert
        $this->assertSame(0, $aggregationTransfer->getTotalRequests());
        $this->assertSame(0, $aggregationTransfer->getTotalTokens());
    }

    public function testGivenPersistedLogsWhenGettingAggregationThenReturnsCorrectTotals(): void
    {
        // Arrange
        $configName = sprintf('test_agg_config_%s', uniqid());
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $configName,
            'is_successful' => true,
            'input_tokens' => 100,
            'output_tokens' => 50,
            'inference_time_ms' => 500,
        ]);
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $configName,
            'is_successful' => true,
            'input_tokens' => 200,
            'output_tokens' => 100,
            'inference_time_ms' => 700,
        ]);
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $configName,
            'is_successful' => false,
            'input_tokens' => 150,
            'output_tokens' => 75,
            'inference_time_ms' => 600,
        ]);

        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName($configName),
            );

        // Act
        $aggregationTransfer = $this->tester->getFacade()->getAiInteractionLogAggregation($criteriaTransfer);

        // Assert
        $this->assertSame(3, $aggregationTransfer->getTotalRequests());
        $this->assertSame(675, $aggregationTransfer->getTotalTokens()); // (100+50) + (200+100) + (150+75)
        $this->assertEqualsWithDelta(66.67, $aggregationTransfer->getSuccessRate(), 0.01); // 2/3 * 100
        $this->assertEqualsWithDelta(600.0, $aggregationTransfer->getAverageInferenceTimeMs(), 0.01); // (500+700+600)/3
    }

    public function testGivenMixedConfigsWhenFilteringByConfigNameThenAggregatesOnlyFiltered(): void
    {
        // Arrange
        $targetConfig = sprintf('test_target_%s', uniqid());
        $otherConfig = sprintf('test_other_%s', uniqid());
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $targetConfig,
            'is_successful' => true,
            'input_tokens' => 100,
            'output_tokens' => 50,
            'inference_time_ms' => 500,
        ]);
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $otherConfig,
            'is_successful' => true,
            'input_tokens' => 999,
            'output_tokens' => 999,
            'inference_time_ms' => 999,
        ]);

        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName($targetConfig),
            );

        // Act
        $aggregationTransfer = $this->tester->getFacade()->getAiInteractionLogAggregation($criteriaTransfer);

        // Assert
        $this->assertSame(1, $aggregationTransfer->getTotalRequests());
        $this->assertSame(150, $aggregationTransfer->getTotalTokens());
    }
}
