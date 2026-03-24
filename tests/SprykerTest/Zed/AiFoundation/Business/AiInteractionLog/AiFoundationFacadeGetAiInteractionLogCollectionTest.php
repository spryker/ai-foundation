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
 * @group AiFoundationFacadeGetAiInteractionLogCollectionTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeGetAiInteractionLogCollectionTest extends Unit
{
    protected AiFoundationBusinessTester $tester;

    public function testGivenNoDataWhenGettingCollectionThenReturnsEmptyCollection(): void
    {
        // Arrange
        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName(sprintf('nonexistent_%s', uniqid())),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->getAiInteractionLogCollection($criteriaTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(0, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs());
    }

    public function testGivenPersistedLogsWhenGettingCollectionThenReturnsPopulatedCollection(): void
    {
        // Arrange
        $configName = sprintf('test_config_%s', uniqid());
        $this->tester->haveAiInteractionLog(['configuration_name' => $configName, 'is_successful' => true]);

        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName($configName),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->getAiInteractionLogCollection($criteriaTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs());
        $this->assertSame($configName, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs()->offsetGet(0)->getConfigurationName());
    }

    public function testGivenMixedSuccessLogsWhenFilteringByIsSuccessfulThenReturnsOnlyMatching(): void
    {
        // Arrange
        $configName = sprintf('test_config_%s', uniqid());
        $this->tester->haveAiInteractionLog(['configuration_name' => $configName, 'is_successful' => true]);
        $this->tester->haveAiInteractionLog(['configuration_name' => $configName, 'is_successful' => false]);

        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName($configName)
                    ->setIsSuccessful(true),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->getAiInteractionLogCollection($criteriaTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs());
        $this->assertTrue($responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs()->offsetGet(0)->getIsSuccessful());
    }

    public function testGivenMultipleConversationsWhenFilteringByConversationReferenceThenReturnsOnlyMatching(): void
    {
        // Arrange
        $configName = sprintf('test_config_%s', uniqid());
        $conversationRef = sprintf('conv_%s', uniqid());
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $configName,
            'is_successful' => true,
            'conversation_reference' => $conversationRef,
        ]);
        $this->tester->haveAiInteractionLog([
            'configuration_name' => $configName,
            'is_successful' => true,
            'conversation_reference' => sprintf('other_%s', uniqid()),
        ]);

        $criteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addConfigurationName($configName)
                    ->addConversationReference($conversationRef),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->getAiInteractionLogCollection($criteriaTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs());
        $this->assertSame($conversationRef, $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs()->offsetGet(0)->getConversationReference());
    }
}
