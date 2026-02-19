<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiWorkflow;

use Codeception\Test\Unit;
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
 * @group AiWorkflow
 * @group Facade
 * @group AiFoundationFacadeAiWorkflowTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeAiWorkflowTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester
     */
    protected $tester;

    public function testCreatesWorkflowItemWithGeneratedId(): void
    {
        // Arrange
        $workflowItem = $this->createValidWorkflowItemTransfer(['prompt' => 'Test prompt', 'user_id' => 123]);
        $request = $this->createCollectionRequest([$workflowItem]);

        // Act
        $response = $this->tester->getFacade()->createAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(1, $response->getAiWorkflowItems());
        $createdItem = $response->getAiWorkflowItems()->offsetGet(0);
        $this->assertNotNull($createdItem->getIdAiWorkflowItem());
        $this->assertEquals(['prompt' => 'Test prompt', 'user_id' => 123], $createdItem->getContextData());
    }

    public function testCreatesMultipleItemsTransactionally(): void
    {
        // Arrange
        $items = [
            $this->createValidWorkflowItemTransfer(['task' => 'task1']),
            $this->createValidWorkflowItemTransfer(['task' => 'task2']),
            $this->createValidWorkflowItemTransfer(['task' => 'task3']),
        ];
        $request = $this->createCollectionRequest($items, true);

        // Act
        $response = $this->tester->getFacade()->createAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(3, $response->getAiWorkflowItems());
        foreach ($response->getAiWorkflowItems() as $item) {
            $this->assertNotNull($item->getIdAiWorkflowItem());
        }
    }

    public function testPreservesComplexContextDataThroughJsonSerialization(): void
    {
        // Arrange
        $complexContext = [
            'prompt' => 'Analyze data',
            'metadata' => ['user' => 'admin', 'timestamp' => 1234567890],
            'config' => ['max_retries' => 3, 'timeout' => 30],
            'nested' => ['level1' => ['level2' => ['level3' => 'deep value']]],
        ];
        $workflowItem = $this->createValidWorkflowItemTransfer($complexContext);

        // Act
        $createdItem = $this->createWorkflowItemInDatabase($workflowItem);
        $readItem = $this->readWorkflowItemById($createdItem->getIdAiWorkflowItemOrFail());

        // Assert
        $this->assertEquals($complexContext, $readItem->getContextData());
        $this->assertIsArray($readItem->getContextData());
    }

    public function testGivenInvalidWorkflowItemWhenCreatingCollectionThenExceptionIsThrown(): void
    {
        // Arrange
        $invalidItem = new AiWorkflowItemTransfer();
        $request = $this->createCollectionRequest([$invalidItem]);

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);
        $this->expectExceptionMessage('Missing required property "contextData"');

        // Act
        $this->tester->getFacade()->createAiWorkflowItemCollection($request);
    }

    public function testGivenMultipleWorkflowItemsWhenFilteringByIdsThenOnlyMatchingItemsAreReturned(): void
    {
        // Arrange
        $item1 = $this->createWorkflowItemInDatabase(['task' => 'task1']);
        $item2 = $this->createWorkflowItemInDatabase(['task' => 'task2']);
        $item3 = $this->createWorkflowItemInDatabase(['task' => 'task3']);
        $criteria = $this->createCriteriaByIds([$item1->getIdAiWorkflowItemOrFail(), $item3->getIdAiWorkflowItemOrFail()]);

        // Act
        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($criteria);

        // Assert
        $this->assertCount(2, $collection->getAiWorkflowItems());
        $ids = $this->extractIds($collection->getAiWorkflowItems()->getArrayCopy());
        $this->assertContains($item1->getIdAiWorkflowItemOrFail(), $ids);
        $this->assertContains($item3->getIdAiWorkflowItemOrFail(), $ids);
        $this->assertNotContains($item2->getIdAiWorkflowItemOrFail(), $ids);
    }

    public function testGivenWorkflowItemsInDifferentStatesWhenFilteringByStateIdsThenOnlyMatchingItemsAreReturned(): void
    {
        // Arrange
        $item1 = $this->createWorkflowItemInDatabase(['state' => 'new']);
        $item2 = $this->createWorkflowItemInDatabase(['state' => 'processing']);
        $stateId = $this->tester->haveAiWorkflowStateMachineState();
        $this->updateItemState($item2, $stateId);
        $criteria = $this->createCriteriaByStateIds([$stateId]);

        // Act
        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($criteria);

        // Assert
        $this->assertGreaterThanOrEqual(1, $collection->getAiWorkflowItems()->count());
        $foundItem2 = false;
        foreach ($collection->getAiWorkflowItems() as $item) {
            if ($item->getIdAiWorkflowItem() === $item2->getIdAiWorkflowItem()) {
                $foundItem2 = true;
                $this->assertEquals($stateId, $item->getFkStateMachineItemState());
            }
        }
        $this->assertTrue($foundItem2);
    }

    public function testGivenNonExistentIdsWhenQueryingCollectionThenEmptyCollectionIsReturned(): void
    {
        // Arrange
        $criteria = $this->createCriteriaByIds([999999, 888888]);

        // Act
        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($criteria);

        // Assert
        $this->assertCount(0, $collection->getAiWorkflowItems());
    }

    public function testGivenWorkflowItemWithContextDataWhenReadingCollectionThenContextDataIsDeserialized(): void
    {
        // Arrange
        $contextData = ['key1' => 'value1', 'key2' => ['nested' => 'value2']];
        $createdItem = $this->createWorkflowItemInDatabase($contextData);

        // Act
        $readItem = $this->readWorkflowItemById($createdItem->getIdAiWorkflowItemOrFail());

        // Assert
        $this->assertIsArray($readItem->getContextData());
        $this->assertEquals($contextData, $readItem->getContextData());
    }

    public function testGivenExistingWorkflowItemWhenUpdatingStateCollectionThenStateReferenceIsUpdated(): void
    {
        // Arrange
        $item = $this->createWorkflowItemInDatabase(['status' => 'initial']);
        $newStateId = $this->tester->haveAiWorkflowStateMachineState();
        $item->setFkStateMachineItemState($newStateId);
        $request = $this->createCollectionRequest([$item], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $updatedItem = $this->readWorkflowItemById($item->getIdAiWorkflowItemOrFail());
        $this->assertEquals($newStateId, $updatedItem->getFkStateMachineItemState());
    }

    public function testGivenMultipleItemsWhenUpdatingStateWithTransactionThenAllUpdatedOrNoneUpdated(): void
    {
        // Arrange
        $item1 = $this->createWorkflowItemInDatabase(['task' => 'task1']);
        $item2 = $this->createWorkflowItemInDatabase(['task' => 'task2']);
        $newStateId = $this->tester->haveAiWorkflowStateMachineState();
        $item1->setFkStateMachineItemState($newStateId);
        $item2->setFkStateMachineItemState($newStateId);
        $request = $this->createCollectionRequest([$item1, $item2], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertEquals($newStateId, $this->readWorkflowItemById($item1->getIdAiWorkflowItemOrFail())->getFkStateMachineItemState());
        $this->assertEquals($newStateId, $this->readWorkflowItemById($item2->getIdAiWorkflowItemOrFail())->getFkStateMachineItemState());
    }

    public function testGivenWorkflowItemWithMissingIdWhenUpdatingStateThenExceptionIsThrown(): void
    {
        // Arrange
        $invalidItem = (new AiWorkflowItemTransfer())->setFkStateMachineItemState(123);
        $request = $this->createCollectionRequest([$invalidItem]);

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);
        $this->expectExceptionMessage('Missing required property "idAiWorkflowItem"');

        // Act
        $this->tester->getFacade()->updateAiWorkflowItemCollection($request);
    }

    public function testGivenExistingWorkflowItemWhenUpdatingContextCollectionThenContextDataIsUpdated(): void
    {
        // Arrange
        $item = $this->createWorkflowItemInDatabase(['original' => 'data']);
        $newContext = ['updated' => 'data', 'timestamp' => time()];
        $item->setContextData($newContext);
        $request = $this->createCollectionRequest([$item], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $updatedItem = $this->readWorkflowItemById($item->getIdAiWorkflowItemOrFail());
        $this->assertEquals($newContext, $updatedItem->getContextData());
    }

    public function testGivenWorkflowItemWithExistingContextWhenUpdatingContextThenNewDataReplacesOldData(): void
    {
        // Arrange
        $item = $this->createWorkflowItemInDatabase(['old_key' => 'old_value', 'shared_key' => 'old']);
        $newContext = ['new_key' => 'new_value', 'shared_key' => 'new'];
        $item->setContextData($newContext);
        $request = $this->createCollectionRequest([$item], true);

        // Act
        $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $updatedItem = $this->readWorkflowItemById($item->getIdAiWorkflowItemOrFail());
        $this->assertEquals($newContext, $updatedItem->getContextData());
        $this->assertArrayNotHasKey('old_key', $updatedItem->getContextData());
    }

    public function testGivenWorkflowItemWhenUpdatingWithMinimalContextThenContextIsUpdatedToMinimal(): void
    {
        // Arrange
        $item = $this->createWorkflowItemInDatabase(['key' => 'value', 'nested' => ['data' => 'complex']]);
        $minimalContext = ['status' => 'minimal'];
        $item->setContextData($minimalContext);
        $request = $this->createCollectionRequest([$item], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $updatedItem = $this->readWorkflowItemById($item->getIdAiWorkflowItemOrFail());
        $this->assertEquals($minimalContext, $updatedItem->getContextData());
        $this->assertArrayNotHasKey('key', $updatedItem->getContextData());
    }

    public function testGivenNonTransactionalCreateWithInvalidItemWhenCreatingThenExceptionIsThrown(): void
    {
        // Arrange
        $validItem = $this->createValidWorkflowItemTransfer(['valid' => 'data']);
        $invalidItem = new AiWorkflowItemTransfer();
        $request = $this->createCollectionRequest([$validItem, $invalidItem], false);

        // Expect
        $this->expectException(RequiredTransferPropertyException::class);
        $this->expectExceptionMessage('Missing required property "contextData"');

        // Act
        $this->tester->getFacade()->createAiWorkflowItemCollection($request);
    }

    public function testGivenNonTransactionalStateUpdateWithInvalidItemWhenUpdatingThenValidItemsAreUpdated(): void
    {
        // Arrange
        $item = $this->createWorkflowItemInDatabase(['test' => 'data']);
        $stateId = $this->tester->haveAiWorkflowStateMachineState();
        $item->setFkStateMachineItemState($stateId);
        $invalidItem = (new AiWorkflowItemTransfer())->setIdAiWorkflowItem(999999)->setFkStateMachineItemState($stateId);
        $request = $this->createCollectionRequest([$item, $invalidItem], false);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertEquals($stateId, $this->readWorkflowItemById($item->getIdAiWorkflowItemOrFail())->getFkStateMachineItemState());
    }

    public function testGivenNonExistentItemWhenUpdatingContextThenItemIsUpdatedWithoutValidation(): void
    {
        // Arrange
        $nonExistentItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999999)
            ->setContextData(['new' => 'data']);
        $request = $this->createCollectionRequest([$nonExistentItem], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
    }

    public function testGivenNonExistentItemWhenUpdatingStateThenItemIsUpdatedWithoutValidation(): void
    {
        // Arrange
        $stateId = $this->tester->haveAiWorkflowStateMachineState();
        $nonExistentItem = (new AiWorkflowItemTransfer())
            ->setIdAiWorkflowItem(999999)
            ->setFkStateMachineItemState($stateId);
        $request = $this->createCollectionRequest([$nonExistentItem], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
    }

    public function testGivenBulkOperationWhenUpdatingMultipleStatesThenAllAreUpdatedSuccessfully(): void
    {
        // Arrange
        $item1 = $this->createWorkflowItemInDatabase(['item' => '1']);
        $item2 = $this->createWorkflowItemInDatabase(['item' => '2']);
        $item3 = $this->createWorkflowItemInDatabase(['item' => '3']);
        $newStateId = $this->tester->haveAiWorkflowStateMachineState();
        $item1->setFkStateMachineItemState($newStateId);
        $item2->setFkStateMachineItemState($newStateId);
        $item3->setFkStateMachineItemState($newStateId);
        $request = $this->createCollectionRequest([$item1, $item2, $item3], true);

        // Act
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        // Assert
        $this->assertTrue($response->getIsSuccessful());
        $this->assertCount(3, $response->getAiWorkflowItems());
        foreach ($response->getAiWorkflowItems() as $item) {
            $this->assertEquals($newStateId, $item->getFkStateMachineItemState());
        }
    }

    protected function createValidWorkflowItemTransfer(array $contextData = []): AiWorkflowItemTransfer
    {
        return (new AiWorkflowItemTransfer())->setContextData($contextData ?: ['test' => 'data']);
    }

    protected function createCollectionRequest(array $items, bool $isTransactional = false): AiWorkflowItemCollectionRequestTransfer
    {
        $request = (new AiWorkflowItemCollectionRequestTransfer())->setIsTransactional($isTransactional);

        foreach ($items as $item) {
            $request->addAiWorkflowItem($item);
        }

        return $request;
    }

    protected function createWorkflowItemInDatabase(AiWorkflowItemTransfer|array $itemOrContext): AiWorkflowItemTransfer
    {
        $workflowItem = $itemOrContext instanceof AiWorkflowItemTransfer
            ? $itemOrContext
            : $this->createValidWorkflowItemTransfer($itemOrContext);

        $request = $this->createCollectionRequest([$workflowItem], true);
        $response = $this->tester->getFacade()->createAiWorkflowItemCollection($request);

        return $response->getAiWorkflowItems()->offsetGet(0);
    }

    protected function readWorkflowItemById(int $idAiWorkflowItem): AiWorkflowItemTransfer
    {
        $criteria = $this->createCriteriaByIds([$idAiWorkflowItem]);
        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($criteria);

        return $collection->getAiWorkflowItems()->offsetGet(0);
    }

    protected function readWorkflowItemByIdOrNull(int $idAiWorkflowItem): ?AiWorkflowItemTransfer
    {
        $criteria = $this->createCriteriaByIds([$idAiWorkflowItem]);
        $collection = $this->tester->getFacade()->getAiWorkflowItemCollection($criteria);

        return $collection->getAiWorkflowItems()->count() > 0
            ? $collection->getAiWorkflowItems()->offsetGet(0)
            : null;
    }

    protected function createCriteriaByIds(array $ids): AiWorkflowItemCriteriaTransfer
    {
        $conditions = new AiWorkflowItemConditionsTransfer();

        foreach ($ids as $id) {
            $conditions->addAiWorkflowItemId($id);
        }

        return (new AiWorkflowItemCriteriaTransfer())->setAiWorkflowItemConditions($conditions);
    }

    protected function createCriteriaByStateIds(array $stateIds): AiWorkflowItemCriteriaTransfer
    {
        $conditions = new AiWorkflowItemConditionsTransfer();

        foreach ($stateIds as $stateId) {
            $conditions->addStateId($stateId);
        }

        return (new AiWorkflowItemCriteriaTransfer())->setAiWorkflowItemConditions($conditions);
    }

    protected function updateItemState(AiWorkflowItemTransfer $item, int $newStateId): AiWorkflowItemTransfer
    {
        $item->setFkStateMachineItemState($newStateId);
        $request = $this->createCollectionRequest([$item], true);
        $response = $this->tester->getFacade()->updateAiWorkflowItemCollection($request);

        return $response->getAiWorkflowItems()->offsetGet(0);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AiWorkflowItemTransfer> $items
     *
     * @return array<int>
     */
    protected function extractIds(array $items): array
    {
        return array_map(
            fn (AiWorkflowItemTransfer $item): int => $item->getIdAiWorkflowItemOrFail(),
            $items,
        );
    }
}
