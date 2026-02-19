<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Communication\Plugin\StateMachine;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Communication\Plugin\StateMachine\AiWorkflowStateMachineHandlerPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Communication
 * @group Plugin
 * @group StateMachine
 * @group AiWorkflowStateMachineHandlerPluginTest
 * Add your own group annotations below this line
 */
class AiWorkflowStateMachineHandlerPluginTest extends Unit
{
    protected const string TEST_PROCESS_NAME = 'TestProcess';

    protected const string TEST_INITIAL_STATE = 'new';

    protected const string TEST_STATE_MACHINE_NAME = 'AiWorkflow';

    /**
     * @var \SprykerTest\Zed\AiFoundation\AiFoundationCommunicationTester
     */
    protected $tester;

    public function testGivenCommandPluginsConfiguredWhenGettingCommandPluginsThenPluginsAreReturned(): void
    {
        // Arrange
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_AI_WORKFLOW_COMMAND, []);
        $plugin = $this->createPlugin();

        // Act
        $commandPlugins = $plugin->getCommandPlugins();

        // Assert
        $this->assertIsArray($commandPlugins);
    }

    public function testGivenConditionPluginsConfiguredWhenGettingConditionPluginsThenPluginsAreReturned(): void
    {
        // Arrange
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_AI_WORKFLOW_CONDITION, []);
        $plugin = $this->createPlugin();

        // Act
        $conditionPlugins = $plugin->getConditionPlugins();

        // Assert
        $this->assertIsArray($conditionPlugins);
    }

    public function testGivenStateMachineNameConfiguredWhenGettingStateMachineNameThenConfiguredNameIsReturned(): void
    {
        // Arrange
        $plugin = $this->createPlugin();

        // Act
        $actualStateMachineName = $plugin->getStateMachineName();

        // Assert
        $this->assertSame(static::TEST_STATE_MACHINE_NAME, $actualStateMachineName);
    }

    public function testGivenActiveProcessesConfiguredWhenGettingActiveProcessesThenProcessListIsReturned(): void
    {
        // Arrange
        $plugin = $this->createPlugin();

        // Act
        $activeProcesses = $plugin->getActiveProcesses();

        // Assert
        $this->assertIsArray($activeProcesses);
    }

    public function testGivenInitialStateMapConfiguredWhenGettingInitialStateForProcessThenCorrectStateIsReturned(): void
    {
        // Arrange
        $configMock = $this->createConfigMock([
            'getAiWorkflowInitialStateForProcess' => static::TEST_INITIAL_STATE,
        ]);
        $this->tester->getFactory()->setConfig($configMock);

        $plugin = $this->createPlugin();

        // Act
        $initialState = $plugin->getInitialStateForProcess(static::TEST_PROCESS_NAME);

        // Assert
        $this->assertSame(static::TEST_INITIAL_STATE, $initialState);
    }

    public function testGivenExistingWorkflowItemWhenStateIsUpdatedThenItemStateIsUpdatedSuccessfully(): void
    {
        // Arrange
        $stateId = $this->tester->haveAiWorkflowStateMachineState();
        $workflowItem = $this->tester->haveAiWorkflowItem(['context_data' => ['test' => 'data']]);

        $stateMachineItemTransfer = (new StateMachineItemTransfer())
            ->setIdentifier($workflowItem->getIdAiWorkflowItem())
            ->setIdItemState($stateId);

        $plugin = $this->createPlugin();

        // Act
        $result = $plugin->itemStateUpdated($stateMachineItemTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testGivenNonExistentWorkflowItemWhenStateIsUpdatedThenFalseIsReturned(): void
    {
        // Arrange
        $stateId = $this->tester->haveAiWorkflowStateMachineState();

        $stateMachineItemTransfer = (new StateMachineItemTransfer())
            ->setIdentifier(999999)
            ->setIdItemState($stateId);

        $plugin = $this->createPlugin();

        // Act
        $result = $plugin->itemStateUpdated($stateMachineItemTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testGivenMultipleWorkflowItemsWhenGettingItemsByStateIdsThenCorrectItemsAreReturned(): void
    {
        // Arrange
        $stateId1 = $this->tester->haveAiWorkflowStateMachineState();
        $stateId2 = $this->tester->haveAiWorkflowStateMachineState();

        $item1 = $this->tester->haveAiWorkflowItem([
            'context_data' => ['item' => '1'],
            'fk_state_machine_item_state' => $stateId1,
        ]);

        $item2 = $this->tester->haveAiWorkflowItem([
            'context_data' => ['item' => '2'],
            'fk_state_machine_item_state' => $stateId2,
        ]);

        $plugin = $this->createPlugin();

        // Act
        $actualItems = $plugin->getStateMachineItemsByStateIds([$stateId1, $stateId2]);

        // Assert
        $this->assertCount(2, $actualItems);
        $this->assertInstanceOf(StateMachineItemTransfer::class, $actualItems[0]);
    }

    public function testGivenEmptyStateIdsWhenGettingItemsByStateIdsThenEmptyArrayIsReturned(): void
    {
        // Arrange
        $plugin = $this->createPlugin();

        // Act
        $actualItems = $plugin->getStateMachineItemsByStateIds([]);

        // Assert
        $this->assertIsArray($actualItems);
        $this->assertEmpty($actualItems);
    }

    public function testGivenNoMatchingItemsWhenGettingItemsByStateIdsThenEmptyArrayIsReturned(): void
    {
        // Arrange
        $stateId = $this->tester->haveAiWorkflowStateMachineState();
        $plugin = $this->createPlugin();

        // Act
        $actualItems = $plugin->getStateMachineItemsByStateIds([$stateId]);

        // Assert
        $this->assertIsArray($actualItems);
        $this->assertEmpty($actualItems);
    }

    protected function createPlugin(): AiWorkflowStateMachineHandlerPlugin
    {
        return new AiWorkflowStateMachineHandlerPlugin();
    }

    /**
     * @param array<string, mixed> $methods
     *
     * @return \Spryker\Zed\AiFoundation\AiFoundationConfig
     */
    protected function createConfigMock(array $methods): AiFoundationConfig
    {
        $configMock = $this->createMock(AiFoundationConfig::class);

        foreach ($methods as $method => $returnValue) {
            $configMock->method($method)->willReturn($returnValue);
        }

        return $configMock;
    }
}
