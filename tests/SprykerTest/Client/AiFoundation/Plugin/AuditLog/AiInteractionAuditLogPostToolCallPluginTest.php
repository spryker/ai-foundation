<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\Plugin\AuditLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\AiFoundation\AiFoundationFactory;
use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface;
use Spryker\Client\AiFoundation\Plugin\AuditLog\AiInteractionAuditLogPostToolCallPlugin;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group Plugin
 * @group AuditLog
 * @group AiInteractionAuditLogPostToolCallPluginTest
 * Add your own group annotations below this line
 */
class AiInteractionAuditLogPostToolCallPluginTest extends Unit
{
    protected const string TOOL_NAME = 'calculator';

    protected const string AI_CONFIGURATION_NAME = 'anthropic_example';

    protected AiFoundationClientTester $tester;

    public function testGivenToolCallWhenPostToolCallRunsThenToolCallIsCollected(): void
    {
        // Arrange
        $aiToolCallTransfer = (new AiToolCallTransfer())
            ->setToolName(static::TOOL_NAME)
            ->setPromptRequest((new PromptRequestTransfer())->setAiConfigurationName(static::AI_CONFIGURATION_NAME));
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->once())
            ->method('collectToolCall')
            ->with($aiToolCallTransfer);

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postToolCall($aiToolCallTransfer);
    }

    public function testGivenSkippedToolCallWhenPostToolCallRunsThenToolCallIsCollected(): void
    {
        // Arrange
        $aiToolCallTransfer = (new AiToolCallTransfer())
            ->setToolName(static::TOOL_NAME)
            ->setIsExecutionAllowed(false);
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->once())
            ->method('collectToolCall')
            ->with($aiToolCallTransfer);

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postToolCall($aiToolCallTransfer);
    }

    public function testGivenToolCallWhenPostToolCallRunsThenCollectedEntriesAreNotFlushed(): void
    {
        // Arrange
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->never())->method('flush');

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postToolCall(new AiToolCallTransfer());
    }

    public function testGivenToolCallWhenPostToolCallRunsThenNoPromptIsCollected(): void
    {
        // Arrange
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->never())->method('collectPrompt');

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postToolCall(new AiToolCallTransfer());
    }

    /**
     * @return \Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAiInteractionLogCollectorMock(): AiInteractionLogCollectorInterface
    {
        return $this->createMock(AiInteractionLogCollectorInterface::class);
    }

    protected function createPlugin(MockObject $aiInteractionLogCollectorMock): AiInteractionAuditLogPostToolCallPlugin
    {
        $aiFoundationFactoryMock = $this->createMock(AiFoundationFactory::class);
        $aiFoundationFactoryMock->method('createAiInteractionLogCollector')->willReturn($aiInteractionLogCollectorMock);

        $plugin = new AiInteractionAuditLogPostToolCallPlugin();
        $plugin->setFactory($aiFoundationFactoryMock);

        return $plugin;
    }
}
