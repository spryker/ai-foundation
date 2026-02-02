<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Spryker\Shared\Kernel\Transfer\Exception\RequiredTransferPropertyException;

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
            ->setContextData($contextData);

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        // Act
        $response = $this->tester->getFacade()->createAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(1, $response->getAiWorkflowItems());

        $createdItem = $response->getAiWorkflowItems()->offsetGet(0);
        $this->assertNotNull($createdItem->getIdAiWorkflowItem());
        $this->assertEquals($contextData, $createdItem->getContextData());
    }

    /**
     * @return void
     */
    public function testGivenExistingWorkflowItemWhenUpdatingContextThenContextIsUpdated(): void
    {
        // Arrange
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setContextData(['initial' => 'data']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem = $createResponse->getAiWorkflowItems()->offsetGet(0);

        $updatedContextData = [
            'initial' => 'data',
            'analysis_result' => 'Analysis completed successfully',
            'success' => true,
        ];

        $createdItem->setContextData($updatedContextData);

        $updateRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($createdItem);

        // Act
        $updateResponse = $this->tester->getFacade()->updateAiWorkflowItemContextCollection($updateRequest);

        // Assert
        $this->assertTrue($updateResponse->getIsSuccessful());
        $this->assertCount(1, $updateResponse->getAiWorkflowItems());

        $updatedItem = $updateResponse->getAiWorkflowItems()->offsetGet(0);
        $this->assertEquals($updatedContextData, $updatedItem->getContextData());
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
            ->setContextData($contextData);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem = $createResponse->getAiWorkflowItems()->offsetGet(0);

        // Act
        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addAiWorkflowItemId($createdItem->getIdAiWorkflowItem());

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollection = $this->tester->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        // Assert
        $this->assertCount(1, $aiWorkflowItemCollection->getAiWorkflowItems());

        $foundTransfer = $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0);
        $this->assertNotNull($foundTransfer);
        $this->assertEquals($contextData, $foundTransfer->getContextData());
    }

    /**
     * @return void
     */
    public function testGivenNonExistentIdWhenFindingByIdThenNullIsReturned(): void
    {
        // Act
        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addAiWorkflowItemId(999999);

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

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
            ->setFkStateMachineItemState(1)
            ->setContextData(['test' => 'data1']);

        $aiWorkflowItemTransfer2 = (new AiWorkflowItemTransfer())
            ->setFkStateMachineItemState(2)
            ->setContextData(['test' => 'data2']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject([$aiWorkflowItemTransfer1, $aiWorkflowItemTransfer2]));

        $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);

        // Act
        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addStateId(1);

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        // Assert
        $this->assertGreaterThanOrEqual(1, $collection->getAiWorkflowItems()->count());

        $found = false;
        foreach ($collection->getAiWorkflowItems() as $item) {
            if ($item->getFkStateMachineItemState() === 1) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * @return void
     */
    public function testGivenTransactionalCreateWithInvalidItemWhenCreatingThenExceptionIsThrown(): void
    {
        // Arrange
        $validItem = (new AiWorkflowItemTransfer())
            ->setContextData(['valid' => 'data']);

        $invalidItem = (new AiWorkflowItemTransfer());

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject([$validItem, $invalidItem]));

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->tester->getFacade()->createAiWorkflowItemCollection($request);
    }

    /**
     * @return void
     */
    public function testGivenNonTransactionalCreateWithInvalidItemWhenCreatingThenExceptionIsThrown(): void
    {
        // Arrange
        $validItem = (new AiWorkflowItemTransfer())
            ->setContextData(['valid' => 'data']);

        $invalidItem = (new AiWorkflowItemTransfer());

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(false)
            ->setAiWorkflowItems(new ArrayObject([$validItem, $invalidItem]));

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->tester->getFacade()->createAiWorkflowItemCollection($request);
    }

    /**
     * @return void
     */
    public function testGivenNonExistentItemWhenUpdatingContextThenItemIsUpdatedWithoutValidation(): void
    {
        // Arrange
        $nonExistentItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999999)
            ->setContextData(['new' => 'data']);

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($nonExistentItem);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemContextCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testGivenExistingItemsWhenDeletingByIdsThenItemsAreRemoved(): void
    {
        // Arrange
        $aiWorkflowItemTransfer1 = (new AiWorkflowItemTransfer())
            ->setContextData(['test' => 'data1']);

        $aiWorkflowItemTransfer2 = (new AiWorkflowItemTransfer())
            ->setContextData(['test' => 'data2']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject([$aiWorkflowItemTransfer1, $aiWorkflowItemTransfer2]));

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem1 = $createResponse->getAiWorkflowItems()->offsetGet(0);
        $createdItem2 = $createResponse->getAiWorkflowItems()->offsetGet(1);

        // Act
        $deleteCriteria = (new AiWorkflowItemCollectionDeleteCriteriaTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItemIds([
                $createdItem1->getIdAiWorkflowItem(),
                $createdItem2->getIdAiWorkflowItem(),
            ]);

        $deleteResponse = $this->tester->getFacade()->deleteAiWorkflowItemCollection($deleteCriteria);

        // Assert
        $this->assertTrue($deleteResponse->getIsSuccessful());
        $this->assertCount(2, $deleteResponse->getAiWorkflowItems());

        $findCriteria = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions(
                (new AiWorkflowItemConditionsTransfer())
                    ->setAiWorkflowItemIds([
                        $createdItem1->getIdAiWorkflowItem(),
                        $createdItem2->getIdAiWorkflowItem(),
                    ]),
            );

        $findResult = $this->tester->getFacade()->getAiWorkflowItemCollection($findCriteria);
        $this->assertCount(0, $findResult->getAiWorkflowItems());
    }

    /**
     * @return void
     */
    public function testGivenBulkOperationWhenCreatingMultipleItemsThenAllArePersistedSuccessfully(): void
    {
        // Arrange
        $items = [
            (new AiWorkflowItemTransfer())->setContextData(['item' => '1']),
            (new AiWorkflowItemTransfer())->setContextData(['item' => '2']),
            (new AiWorkflowItemTransfer())->setContextData(['item' => '3']),
        ];

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject($items));

        // Act
        $response = $this->tester->getFacade()->createAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(3, $response->getAiWorkflowItems());

        foreach ($response->getAiWorkflowItems() as $item) {
            $this->assertNotNull($item->getIdAiWorkflowItem());
        }
    }

    /**
     * @return void
     */
    public function testGivenExistingWorkflowItemWhenUpdatingStateThenStateIsUpdated(): void
    {
        // Arrange
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setFkStateMachineItemState(1)
            ->setContextData(['test' => 'data']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem = $createResponse->getAiWorkflowItems()->offsetGet(0);

        $createdItem->setFkStateMachineItemState(2);

        $updateRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($createdItem);

        // Act
        $updateResponse = $this->tester->getFacade()->updateAiWorkflowItemStateCollection($updateRequest);

        // Assert
        $this->assertTrue($updateResponse->getIsSuccessful());
        $this->assertCount(1, $updateResponse->getAiWorkflowItems());

        $updatedItem = $updateResponse->getAiWorkflowItems()->offsetGet(0);
        $this->assertEquals(2, $updatedItem->getFkStateMachineItemState());
    }

    /**
     * @return void
     */
    public function testGivenTransactionalStateUpdateWithInvalidItemWhenUpdatingThenExceptionIsThrown(): void
    {
        // Arrange
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setFkStateMachineItemState(1)
            ->setContextData(['test' => 'data']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem = $createResponse->getAiWorkflowItems()->offsetGet(0);

        $createdItem->setFkStateMachineItemState(2);

        $invalidItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999);

        $updateRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject([$createdItem, $invalidItem]));

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->tester->getFacade()->updateAiWorkflowItemStateCollection($updateRequest);
    }

    /**
     * @return void
     */
    public function testGivenNonTransactionalStateUpdateWithInvalidItemWhenUpdatingThenExceptionIsThrown(): void
    {
        // Arrange
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setFkStateMachineItemState(1)
            ->setContextData(['test' => 'data']);

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($aiWorkflowItemTransfer);

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);
        $createdItem = $createResponse->getAiWorkflowItems()->offsetGet(0);

        $createdItem->setFkStateMachineItemState(2);

        $invalidItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999);

        $updateRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(false)
            ->setAiWorkflowItems(new ArrayObject([$createdItem, $invalidItem]));

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->tester->getFacade()->updateAiWorkflowItemStateCollection($updateRequest);
    }

    /**
     * @return void
     */
    public function testGivenNonExistentItemWhenUpdatingStateThenItemIsUpdatedWithoutValidation(): void
    {
        // Arrange
        $nonExistentItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999999)
            ->setFkStateMachineItemState(5);

        $request = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addAiWorkflowItem($nonExistentItem);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemStateCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testGivenBulkOperationWhenUpdatingMultipleStatesThenAllAreUpdatedSuccessfully(): void
    {
        // Arrange
        $items = [
            (new AiWorkflowItemTransfer())->setFkStateMachineItemState(1)->setContextData(['item' => '1']),
            (new AiWorkflowItemTransfer())->setFkStateMachineItemState(1)->setContextData(['item' => '2']),
            (new AiWorkflowItemTransfer())->setFkStateMachineItemState(1)->setContextData(['item' => '3']),
        ];

        $createRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject($items));

        $createResponse = $this->tester->getFacade()->createAiWorkflowItemCollection($createRequest);

        $itemsToUpdate = [];
        foreach ($createResponse->getAiWorkflowItems() as $item) {
            $item->setFkStateMachineItemState(2);
            $itemsToUpdate[] = $item;
        }

        $updateRequest = (new AiWorkflowItemCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->setAiWorkflowItems(new ArrayObject($itemsToUpdate));

        // Act
        $updateResponse = $this->tester->getFacade()->updateAiWorkflowItemStateCollection($updateRequest);

        // Assert
        $this->assertTrue($updateResponse->getIsSuccessful());
        $this->assertCount(3, $updateResponse->getAiWorkflowItems());

        foreach ($updateResponse->getAiWorkflowItems() as $item) {
            $this->assertEquals(2, $item->getFkStateMachineItemState());
        }
    }
}
