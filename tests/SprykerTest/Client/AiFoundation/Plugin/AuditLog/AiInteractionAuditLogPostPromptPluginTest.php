<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\Plugin\AuditLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\AiFoundation\AiFoundationFactory;
use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface;
use Spryker\Client\AiFoundation\Plugin\AuditLog\AiInteractionAuditLogPostPromptPlugin;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group Plugin
 * @group AuditLog
 * @group AiInteractionAuditLogPostPromptPluginTest
 * Add your own group annotations below this line
 */
class AiInteractionAuditLogPostPromptPluginTest extends Unit
{
    protected const string AI_CONFIGURATION_NAME = 'anthropic_example';

    protected AiFoundationClientTester $tester;

    public function testGivenRequestAndResponseWhenPostPromptRunsThenPromptIsCollectedWithBoth(): void
    {
        // Arrange
        $promptRequestTransfer = (new PromptRequestTransfer())->setAiConfigurationName(static::AI_CONFIGURATION_NAME);
        $promptResponseTransfer = (new PromptResponseTransfer())->setIsSuccessful(true);
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->once())
            ->method('collectPrompt')
            ->with($promptRequestTransfer, $promptResponseTransfer);

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postPrompt($promptRequestTransfer, $promptResponseTransfer);
    }

    public function testGivenCollectedEntriesWhenPostPromptRunsThenTheyAreFlushedOnce(): void
    {
        // Arrange
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->once())->method('flush');

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postPrompt(new PromptRequestTransfer(), new PromptResponseTransfer());
    }

    public function testGivenRequestAndResponseWhenPostPromptRunsThenPromptIsCollectedBeforeFlushing(): void
    {
        // Arrange
        $invokedMethodNames = [];
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();
        $this->trackInvocations($aiInteractionLogCollectorMock, $invokedMethodNames);

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postPrompt(new PromptRequestTransfer(), new PromptResponseTransfer());

        // Assert
        $this->assertSame(['collectPrompt', 'flush'], $invokedMethodNames);
    }

    public function testGivenRequestAndResponseWhenPostPromptRunsThenNoToolCallIsCollected(): void
    {
        // Arrange
        $aiInteractionLogCollectorMock = $this->createAiInteractionLogCollectorMock();

        // Assert
        $aiInteractionLogCollectorMock->expects($this->never())->method('collectToolCall');

        // Act
        $this->createPlugin($aiInteractionLogCollectorMock)->postPrompt(new PromptRequestTransfer(), new PromptResponseTransfer());
    }

    /**
     * @param array<string> $invokedMethodNames
     */
    protected function trackInvocations(MockObject $aiInteractionLogCollectorMock, array &$invokedMethodNames): void
    {
        $aiInteractionLogCollectorMock->method('collectPrompt')
            ->willReturnCallback(function () use (&$invokedMethodNames): void {
                $invokedMethodNames[] = 'collectPrompt';
            });

        $aiInteractionLogCollectorMock->method('flush')
            ->willReturnCallback(function () use (&$invokedMethodNames): AiInteractionLogCollectionResponseTransfer {
                $invokedMethodNames[] = 'flush';

                return new AiInteractionLogCollectionResponseTransfer();
            });
    }

    /**
     * @return \Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAiInteractionLogCollectorMock(): AiInteractionLogCollectorInterface
    {
        $aiInteractionLogCollectorMock = $this->createMock(AiInteractionLogCollectorInterface::class);
        $aiInteractionLogCollectorMock->method('flush')->willReturn(new AiInteractionLogCollectionResponseTransfer());

        return $aiInteractionLogCollectorMock;
    }

    protected function createPlugin(MockObject $aiInteractionLogCollectorMock): AiInteractionAuditLogPostPromptPlugin
    {
        $aiFoundationFactoryMock = $this->createMock(AiFoundationFactory::class);
        $aiFoundationFactoryMock->method('createAiInteractionLogCollector')->willReturn($aiInteractionLogCollectorMock);

        $plugin = new AiInteractionAuditLogPostPromptPlugin();
        $plugin->setFactory($aiFoundationFactoryMock);

        return $plugin;
    }
}
