<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\AuditLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollector;
use Spryker\Client\AiFoundation\AuditLog\AiInteractionLogCollectorInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group AuditLog
 * @group AiInteractionLogCollectorTest
 * Add your own group annotations below this line
 */
class AiInteractionLogCollectorTest extends Unit
{
    protected const string AI_CONFIGURATION_NAME = 'buyer_assistant';

    protected const string CONVERSATION_REFERENCE = 'ba_test_reference';

    protected const string PROMPT_MESSAGE = 'find cameras';

    protected const string TOOL_NAME_SEARCH = 'catalog_search';

    protected const string TOOL_NAME_DISPLAY = 'display_products';

    protected AiFoundationClientTester $tester;

    /**
     * @var array<\Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer>
     */
    protected array $sentRequestTransfers = [];

    protected function _before(): void
    {
        parent::_before();

        $this->sentRequestTransfers = [];
        $this->createCollector()->clear();
    }

    protected function _after(): void
    {
        $this->createCollector()->clear();

        parent::_after();
    }

    public function testGivenToolCallsCollectedByEarlierCollectorsWhenFlushingThenAllEntriesAreSentInOneRequest(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $this->createCollector()->collectToolCall($this->createAiToolCallTransfer(static::TOOL_NAME_SEARCH, $promptRequestTransfer));
        $this->createCollector()->collectToolCall($this->createAiToolCallTransfer(static::TOOL_NAME_DISPLAY, $promptRequestTransfer));

        // Act
        $aiInteractionLogCollector = $this->createCollector();
        $aiInteractionLogCollector->collectPrompt($promptRequestTransfer, $this->createPromptResponseTransfer());
        $aiInteractionLogCollectionResponseTransfer = $aiInteractionLogCollector->flush();

        // Assert
        $this->assertTrue($aiInteractionLogCollectionResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $this->sentRequestTransfers);

        $aiInteractionLogTransfers = $this->sentRequestTransfers[0]
            ->getAiInteractionLogCollectionOrFail()
            ->getAiInteractionLogs()
            ->getArrayCopy();

        $this->assertCount(3, $aiInteractionLogTransfers);
        $this->assertStringContainsString(static::TOOL_NAME_SEARCH, (string)$aiInteractionLogTransfers[0]->getMetadata());
        $this->assertStringContainsString(static::TOOL_NAME_DISPLAY, (string)$aiInteractionLogTransfers[1]->getMetadata());
        $this->assertStringContainsString('POST_PROMPT', (string)$aiInteractionLogTransfers[2]->getMetadata());
    }

    public function testGivenFlushedTurnWhenFlushingSecondTurnThenOnlyItsOwnEntriesAreSent(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $this->createCollector()->collectToolCall($this->createAiToolCallTransfer(static::TOOL_NAME_SEARCH, $promptRequestTransfer));
        $this->collectPromptAndFlush($promptRequestTransfer);

        // Act
        $this->collectPromptAndFlush($promptRequestTransfer);

        // Assert
        $this->assertCount(2, $this->sentRequestTransfers);
        $this->assertCount(
            1,
            $this->sentRequestTransfers[1]->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs(),
        );
    }

    public function testGivenNoCollectedEntriesWhenFlushingThenNothingIsSent(): void
    {
        // Act
        $aiInteractionLogCollectionResponseTransfer = $this->createCollector()->flush();

        // Assert
        $this->assertTrue($aiInteractionLogCollectionResponseTransfer->getIsSuccessful());
        $this->assertCount(0, $aiInteractionLogCollectionResponseTransfer->getErrors());
        $this->assertCount(0, $this->sentRequestTransfers);
    }

    public function testGivenToolCallWhenCollectingThenNothingIsSent(): void
    {
        // Act
        $this->createCollector()->collectToolCall(
            $this->createAiToolCallTransfer(static::TOOL_NAME_SEARCH, $this->createPromptRequestTransfer()),
        );

        // Assert
        $this->assertCount(0, $this->sentRequestTransfers);
    }

    protected function collectPromptAndFlush(PromptRequestTransfer $promptRequestTransfer): void
    {
        $aiInteractionLogCollector = $this->createCollector();
        $aiInteractionLogCollector->collectPrompt($promptRequestTransfer, $this->createPromptResponseTransfer());
        $aiInteractionLogCollector->flush();
    }

    protected function createCollector(): AiInteractionLogCollectorInterface
    {
        return new AiInteractionLogCollector(
            $this->createAiFoundationStubMock(),
        );
    }

    /**
     * @return \Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAiFoundationStubMock(): AiFoundationStubInterface
    {
        $aiFoundationStubMock = $this->createMock(AiFoundationStubInterface::class);
        $aiFoundationStubMock->method('createAiInteractionLogCollection')
            ->willReturnCallback(
                function (AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer): AiInteractionLogCollectionResponseTransfer {
                    $this->sentRequestTransfers[] = $aiInteractionLogCollectionRequestTransfer;

                    return (new AiInteractionLogCollectionResponseTransfer())->setIsSuccessful(true);
                },
            );

        return $aiFoundationStubMock;
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::AI_CONFIGURATION_NAME)
            ->setConversationReference(static::CONVERSATION_REFERENCE)
            ->setPromptMessage((new PromptMessageTransfer())->setContent(static::PROMPT_MESSAGE));
    }

    protected function createPromptResponseTransfer(): PromptResponseTransfer
    {
        return (new PromptResponseTransfer())
            ->setIsSuccessful(true)
            ->setProvider('openai')
            ->setModel('gpt-4o-mini')
            ->setStreamedContent('Here are some cameras.');
    }

    protected function createAiToolCallTransfer(
        string $toolName,
        PromptRequestTransfer $promptRequestTransfer
    ): AiToolCallTransfer {
        return (new AiToolCallTransfer())
            ->setToolName($toolName)
            ->setPromptRequest($promptRequestTransfer)
            ->setIsExecutionAllowed(true)
            ->setToolResult('{}');
    }
}
