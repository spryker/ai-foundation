<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiWorkflowTest
 * Add your own group annotations below this line
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 */
class AiWorkflowTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testGivenNewWorkflowItemWithContextWhenCreatingThenItemIsPersistedWithSerializedContext(): void
    {
        // Arrange
        $contextData = [
            'prompt' => 'Test prompt',
            'input' => 'Test input data',
        ];

        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setName('Test Workflow')
            ->setContextData($contextData);

        // Act
        $createdTransfer = $this->tester->getFacade()->createAiWorkflowItem($aiWorkflowItemTransfer);

        // Assert
        $this->assertNotNull($createdTransfer->getIdAiWorkflowItem());
        $this->assertEquals('Test Workflow', $createdTransfer->getName());
        $this->assertEquals($contextData, $createdTransfer->getContextData());
    }

    /**
     * @return void
     */
    public function testGivenExistingWorkflowItemWhenUpdatingContextThenContextIsUpdated(): void
    {
        // Arrange
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setName('Test Workflow')
            ->setContextData(['initial' => 'data']);

        $createdTransfer = $this->tester->getFacade()->createAiWorkflowItem($aiWorkflowItemTransfer);

        $updatedContextData = [
            'initial' => 'data',
            'analysis_result' => 'Analysis completed successfully',
            'success' => true,
        ];

        $createdTransfer->setContextData($updatedContextData);

        // Act
        $updatedTransfer = $this->tester->getFacade()->updateAiWorkflowItemContext($createdTransfer);

        // Assert
        $this->assertEquals($updatedContextData, $updatedTransfer->getContextData());
    }

    /**
     * @return void
     */
    public function testGivenWorkflowItemIdWhenFindingByIdThenItemIsRetrievedWithDeserializedContext(): void
    {
        // Arrange
        $contextData = [
            'prompt' => 'Find me',
            'result' => 'Found it',
        ];

        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setName('Findable Workflow')
            ->setContextData($contextData);

        $createdTransfer = $this->tester->getFacade()->createAiWorkflowItem($aiWorkflowItemTransfer);

        // Act
        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->addAiWorkflowItemId($createdTransfer->getIdAiWorkflowItem());

        $aiWorkflowItemCollection = $this->tester->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);
        $foundTransfer = $aiWorkflowItemCollection->getAiWorkflowItems()->count() > 0
            ? $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0)
            : null;

        // Assert
        $this->assertNotNull($foundTransfer);
        $this->assertEquals('Findable Workflow', $foundTransfer->getName());
        $this->assertEquals($contextData, $foundTransfer->getContextData());
    }

    /**
     * @return void
     */
    public function testGivenNonExistentIdWhenFindingByIdThenNullIsReturned(): void
    {
        // Act
        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->addAiWorkflowItemId(999999);

        $aiWorkflowItemCollection = $this->tester->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        // Assert
        $this->assertCount(0, $aiWorkflowItemCollection->getAiWorkflowItems());
    }

    /**
     * @return void
     */
    public function testGivenWorkflowItemsWithStateIdsWhenQueryingByStateIdsThenCorrectItemsAreReturned(): void
    {
        // Arrange
        $aiWorkflowItemTransfer1 = (new AiWorkflowItemTransfer())
            ->setName('Workflow 1')
            ->setFkStateMachineItemState(1)
            ->setContextData(['test' => 'data1']);

        $aiWorkflowItemTransfer2 = (new AiWorkflowItemTransfer())
            ->setName('Workflow 2')
            ->setFkStateMachineItemState(2)
            ->setContextData(['test' => 'data2']);

        $this->tester->getFacade()->createAiWorkflowItem($aiWorkflowItemTransfer1);
        $this->tester->getFacade()->createAiWorkflowItem($aiWorkflowItemTransfer2);

        // Act
        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->addStateId(1);

        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        // Assert
        $this->assertCount(1, $collection->getAiWorkflowItems());
        $this->assertEquals('Workflow 1', $collection->getAiWorkflowItems()->offsetGet(0)->getName());
    }
}
