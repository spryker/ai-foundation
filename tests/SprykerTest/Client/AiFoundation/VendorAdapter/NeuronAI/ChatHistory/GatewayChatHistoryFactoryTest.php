<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use NeuronAI\Chat\Messages\UserMessage;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactory;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group VendorAdapter
 * @group NeuronAI
 * @group ChatHistory
 * @group GatewayChatHistoryFactoryTest
 * Add your own group annotations below this line
 */
class GatewayChatHistoryFactoryTest extends Unit
{
    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const string MESSAGE_CONTENT = 'Restored message';

    protected AiFoundationClientTester $tester;

    public function testGivenStoredMessagesWhenCreatingGatewayChatHistoryThenMessagesAreRestored(): void
    {
        // Arrange
        $originalMessages = json_encode([(new UserMessage(static::MESSAGE_CONTENT))->jsonSerialize()]);
        $chatSessionResponseTransfer = $this->createChatSessionResponseTransfer((string)$originalMessages);

        // Act
        $gatewayChatHistory = $this->createFactory()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $chatSessionResponseTransfer,
        );

        // Assert
        $this->assertCount(1, $gatewayChatHistory->getMessages());
        $this->assertSame(static::MESSAGE_CONTENT, $gatewayChatHistory->getMessages()[0]->getContent());
    }

    /**
     * @dataProvider provideUnusableOriginalMessages
     */
    public function testGivenUnusableStoredMessagesWhenCreatingGatewayChatHistoryThenHistoryIsEmpty(?string $originalMessages): void
    {
        // Arrange
        $chatSessionResponseTransfer = $this->createChatSessionResponseTransfer($originalMessages);

        // Act
        $gatewayChatHistory = $this->createFactory()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $chatSessionResponseTransfer,
        );

        // Assert
        $this->assertSame([], $gatewayChatHistory->getMessages());
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public function provideUnusableOriginalMessages(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'invalid json' => ['{not-json'],
            'json scalar' => ['"a string"'],
        ];
    }

    public function testGivenNonArrayStoredEntriesWhenCreatingGatewayChatHistoryThenTheyAreSkipped(): void
    {
        // Arrange
        $originalMessages = json_encode([
            (new UserMessage(static::MESSAGE_CONTENT))->jsonSerialize(),
            'not-an-array',
        ]);

        // Act
        $gatewayChatHistory = $this->createFactory()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $this->createChatSessionResponseTransfer((string)$originalMessages),
        );

        // Assert
        $this->assertCount(1, $gatewayChatHistory->getMessages());
    }

    protected function createChatSessionRequestTransfer(): ChatSessionRequestTransfer
    {
        return (new ChatSessionRequestTransfer())
            ->setConversationReference(static::CONVERSATION_REFERENCE);
    }

    protected function createChatSessionResponseTransfer(?string $originalMessages): ChatSessionResponseTransfer
    {
        return (new ChatSessionResponseTransfer())
            ->setConversationHistory(
                (new ConversationHistoryTransfer())->setOriginalMessages($originalMessages),
            );
    }

    protected function createFactory(): GatewayChatHistoryFactory
    {
        return new GatewayChatHistoryFactory($this->createMock(AiFoundationStubInterface::class));
    }
}
