<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiInteractionLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionTransfer;
use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
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
 * @group AiFoundationFacadeCreateAiInteractionLogCollectionTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeCreateAiInteractionLogCollectionTest extends Unit
{
    protected AiFoundationBusinessTester $tester;

    public function testGivenSingleLogTransferWhenCreatingCollectionThenIdIsGeneratedAndResponseIsSuccessful(): void
    {
        // Arrange
        $aiInteractionLogTransfer = $this->createAiInteractionLogTransfer();
        $request = $this->createCollectionRequest([$aiInteractionLogTransfer]);

        // Act
        $response = $this->tester->getFacade()->createAiInteractionLogCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(1, $response->getAiInteractionLogCollection()->getAiInteractionLogs());
        $this->assertNotNull($response->getAiInteractionLogCollection()->getAiInteractionLogs()->offsetGet(0)->getIdAiInteractionLog());
    }

    public function testGivenMultipleLogTransfersWhenCreatingCollectionThenAllIdsAreGenerated(): void
    {
        // Arrange
        $request = $this->createCollectionRequest([
            $this->createAiInteractionLogTransfer(),
            $this->createAiInteractionLogTransfer(),
        ]);

        // Act
        $response = $this->tester->getFacade()->createAiInteractionLogCollection($request);

        // Assert
        $logs = $response->getAiInteractionLogCollection()->getAiInteractionLogs();
        $this->assertCount(2, $logs);
        $this->assertNotNull($logs->offsetGet(0)->getIdAiInteractionLog());
        $this->assertNotNull($logs->offsetGet(1)->getIdAiInteractionLog());
        $this->assertNotSame($logs->offsetGet(0)->getIdAiInteractionLog(), $logs->offsetGet(1)->getIdAiInteractionLog());
    }

    public function testGivenLogTransferWhenCreatingCollectionThenDataIsPersistedToDatabase(): void
    {
        // Arrange
        $conversationReference = sprintf('test-create-log-%s', uniqid());
        $aiInteractionLogTransfer = $this->createAiInteractionLogTransfer()
            ->setConversationReference($conversationReference)
            ->setPrompt('Test prompt')
            ->setResponse('Test response')
            ->setIsSuccessful(true);

        $request = $this->createCollectionRequest([$aiInteractionLogTransfer]);

        // Act
        $this->tester->getFacade()->createAiInteractionLogCollection($request);

        // Assert
        $entity = SpyAiInteractionLogQuery::create()
            ->filterByConversationReference($conversationReference)
            ->findOne();

        $this->assertNotNull($entity);
        $this->assertSame('Test prompt', $entity->getPrompt());
        $this->assertSame('Test response', $entity->getResponse());
        $this->assertTrue($entity->getIsSuccessful());
    }

    protected function createAiInteractionLogTransfer(): AiInteractionLogTransfer
    {
        return (new AiInteractionLogTransfer())
            ->setConfigurationName('test_config')
            ->setProvider('test_provider')
            ->setModel('test_model')
            ->setIsSuccessful(true);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AiInteractionLogTransfer> $logs
     */
    protected function createCollectionRequest(array $logs): AiInteractionLogCollectionRequestTransfer
    {
        $collection = new AiInteractionLogCollectionTransfer();

        foreach ($logs as $log) {
            $collection->addAiInteractionLog($log);
        }

        return (new AiInteractionLogCollectionRequestTransfer())
            ->setAiInteractionLogCollection($collection);
    }
}
