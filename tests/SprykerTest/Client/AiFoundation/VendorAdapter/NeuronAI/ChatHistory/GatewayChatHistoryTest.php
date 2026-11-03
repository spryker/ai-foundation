<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\UserMessage;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistory;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\ChatHistoryReasoningFilter;
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
 * @group GatewayChatHistoryTest
 * Add your own group annotations below this line
 */
class GatewayChatHistoryTest extends Unit
{
    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const string MESSAGE_CONTENT = 'Hello there';

    protected const int CONTEXT_WINDOW = 50000;

    protected const string PROVIDER_NAME_BEDROCK = 'bedrock';

    protected const string PROVIDER_NAME_OPENAI_RESPONSES = 'openai_responses';

    protected const string MODEL_NAME = 'test-model';

    protected const string REASONING_CONTENT = 'Thinking about the answer';

    protected const string REASONING_SIGNATURE = 'reasoning-signature';

    protected AiFoundationClientTester $tester;

    public function testGivenStoredMessagesWhenConstructingGatewayChatHistoryThenMessagesAreRestored(): void
    {
        // Arrange
        $serializedMessages = [$this->createSerializedUserMessage(static::MESSAGE_CONTENT)];

        // Act
        $gatewayChatHistory = $this->createGatewayChatHistory(
            $this->createAiFoundationStubMock(),
            $serializedMessages,
        );

        // Assert
        $this->assertCount(1, $gatewayChatHistory->getMessages());
        $this->assertSame(static::MESSAGE_CONTENT, $gatewayChatHistory->getMessages()[0]->getContent());
    }

    public function testGivenUndeserializableStoredMessagesWhenConstructingGatewayChatHistoryThenTheyAreDropped(): void
    {
        // Arrange
        $serializedMessages = [
            $this->createSerializedUserMessage(static::MESSAGE_CONTENT),
            ['role' => 'not-a-known-role'],
        ];

        // Act
        $gatewayChatHistory = $this->createGatewayChatHistory(
            $this->createAiFoundationStubMock(),
            $serializedMessages,
        );

        // Assert
        $this->assertCount(1, $gatewayChatHistory->getMessages());
        $this->assertSame(static::MESSAGE_CONTENT, $gatewayChatHistory->getMessages()[0]->getContent());
    }

    public function testGivenStoredMessagesWhenFlushingThenTheyAreSentUnderConversationReference(): void
    {
        // Arrange
        $capturedRequestTransfer = null;
        $aiFoundationStubMock = $this->createAiFoundationStubMock();
        $aiFoundationStubMock->expects($this->once())
            ->method('saveConversationHistoryCollection')
            ->willReturnCallback(function (ConversationHistoryCollectionRequestTransfer $requestTransfer) use (&$capturedRequestTransfer): ConversationHistoryCollectionResponseTransfer {
                $capturedRequestTransfer = $requestTransfer;

                return new ConversationHistoryCollectionResponseTransfer();
            });

        $gatewayChatHistory = $this->createGatewayChatHistory(
            $aiFoundationStubMock,
            [$this->createSerializedUserMessage(static::MESSAGE_CONTENT)],
        );

        // Act
        $gatewayChatHistory->flush();

        // Assert
        $this->assertNotNull($capturedRequestTransfer);
        $this->assertSame(static::CONVERSATION_REFERENCE, $capturedRequestTransfer->getConversationHistories()->offsetGet(0)->getConversationReference());
        $this->assertStringContainsString(static::MESSAGE_CONTENT, (string)$capturedRequestTransfer->getConversationHistories()->offsetGet(0)->getOriginalMessages());
    }

    public function testGivenNoStoredMessagesWhenFlushingThenEmptyMessageListIsSent(): void
    {
        // Arrange
        $capturedRequestTransfer = null;
        $aiFoundationStubMock = $this->createAiFoundationStubMock();
        $aiFoundationStubMock->method('saveConversationHistoryCollection')
            ->willReturnCallback(function (ConversationHistoryCollectionRequestTransfer $requestTransfer) use (&$capturedRequestTransfer): ConversationHistoryCollectionResponseTransfer {
                $capturedRequestTransfer = $requestTransfer;

                return new ConversationHistoryCollectionResponseTransfer();
            });

        $gatewayChatHistory = $this->createGatewayChatHistory($aiFoundationStubMock);

        // Act
        $gatewayChatHistory->flush();

        // Assert
        $this->assertNotNull($capturedRequestTransfer);
        $this->assertSame('[]', $capturedRequestTransfer->getConversationHistories()->offsetGet(0)->getOriginalMessages());
    }

    public function testGivenZedResponseWhenFlushingThenThatResponseIsReturned(): void
    {
        // Arrange
        $conversationHistoryCollectionResponseTransfer = new ConversationHistoryCollectionResponseTransfer();
        $aiFoundationStubMock = $this->createAiFoundationStubMock();
        $aiFoundationStubMock->method('saveConversationHistoryCollection')->willReturn($conversationHistoryCollectionResponseTransfer);

        $gatewayChatHistory = $this->createGatewayChatHistory($aiFoundationStubMock);

        // Act
        $resultTransfer = $gatewayChatHistory->flush();

        // Assert
        $this->assertSame($conversationHistoryCollectionResponseTransfer, $resultTransfer);
    }

    public function testGivenAddedMessageWhenFlushingThenItIsSentToZed(): void
    {
        // Arrange
        $capturedRequestTransfer = null;
        $aiFoundationStubMock = $this->createAiFoundationStubMock();
        $aiFoundationStubMock->expects($this->once())
            ->method('saveConversationHistoryCollection')
            ->willReturnCallback(function (ConversationHistoryCollectionRequestTransfer $requestTransfer) use (&$capturedRequestTransfer): ConversationHistoryCollectionResponseTransfer {
                $capturedRequestTransfer = $requestTransfer;

                return new ConversationHistoryCollectionResponseTransfer();
            });

        $gatewayChatHistory = $this->createGatewayChatHistory($aiFoundationStubMock);

        // Act
        $gatewayChatHistory->addMessage(new UserMessage(static::MESSAGE_CONTENT));
        $gatewayChatHistory->flush();

        // Assert
        $this->assertNotNull($capturedRequestTransfer);

        $originalMessages = json_decode((string)$capturedRequestTransfer->getConversationHistories()->offsetGet(0)->getOriginalMessages(), true);

        $this->assertCount(1, $originalMessages);
        $this->assertStringContainsString(static::MESSAGE_CONTENT, (string)json_encode($originalMessages[0]));
    }

    public function testGivenStoredMessagesWhenConstructingGatewayChatHistoryThenNothingIsSent(): void
    {
        // Arrange
        $aiFoundationStubMock = $this->createAiFoundationStubMock();

        // Assert
        $aiFoundationStubMock->expects($this->never())->method('saveConversationHistoryCollection');

        // Act
        $this->createGatewayChatHistory(
            $aiFoundationStubMock,
            [$this->createSerializedUserMessage(static::MESSAGE_CONTENT)],
        );
    }

    public function testGivenReasoningOfAnotherProviderWhenConstructingGatewayChatHistoryThenReasoningIsRemoved(): void
    {
        // Arrange
        $originGatewayChatHistory = $this->createProviderAwareGatewayChatHistory(static::PROVIDER_NAME_OPENAI_RESPONSES);
        $originGatewayChatHistory->addMessage(new UserMessage(static::MESSAGE_CONTENT));
        $originGatewayChatHistory->addMessage($this->createAssistantMessageWithReasoning());

        // Act
        $gatewayChatHistory = $this->createProviderAwareGatewayChatHistory(
            static::PROVIDER_NAME_BEDROCK,
            $this->serializeMessages($originGatewayChatHistory->getMessages()),
        );

        // Assert
        $this->assertCount(2, $gatewayChatHistory->getMessages());
        $this->assertNull($gatewayChatHistory->getMessages()[1]->getReasoning());
        $this->assertSame(static::MESSAGE_CONTENT, $gatewayChatHistory->getMessages()[1]->getContent());
    }

    public function testGivenReasoningOfSameProviderWhenConstructingGatewayChatHistoryThenReasoningIsKept(): void
    {
        // Arrange
        $originGatewayChatHistory = $this->createProviderAwareGatewayChatHistory(static::PROVIDER_NAME_BEDROCK);
        $originGatewayChatHistory->addMessage(new UserMessage(static::MESSAGE_CONTENT));
        $originGatewayChatHistory->addMessage($this->createAssistantMessageWithReasoning());

        // Act
        $gatewayChatHistory = $this->createProviderAwareGatewayChatHistory(
            static::PROVIDER_NAME_BEDROCK,
            $this->serializeMessages($originGatewayChatHistory->getMessages()),
        );

        // Assert
        $reasoningContent = $gatewayChatHistory->getMessages()[1]->getReasoning();
        $this->assertNotNull($reasoningContent);
        $this->assertSame(static::REASONING_SIGNATURE, $reasoningContent->id);
    }

    public function testGivenReasoningWithoutOriginWhenConstructingGatewayChatHistoryThenReasoningIsRemoved(): void
    {
        // Arrange
        $serializedMessages = $this->serializeMessages([$this->createAssistantMessageWithReasoning()]);

        // Act
        $gatewayChatHistory = $this->createProviderAwareGatewayChatHistory(static::PROVIDER_NAME_BEDROCK, $serializedMessages);

        // Assert
        $this->assertNull($gatewayChatHistory->getMessages()[0]->getReasoning());
    }

    /**
     * @return array<string, mixed>
     */
    protected function createSerializedUserMessage(string $content): array
    {
        return json_decode((string)json_encode((new UserMessage($content))->jsonSerialize()), true);
    }

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     */
    protected function createGatewayChatHistory(
        AiFoundationStubInterface $aiFoundationStubMock,
        array $serializedMessages = []
    ): GatewayChatHistory {
        return new GatewayChatHistory(
            $aiFoundationStubMock,
            static::CONVERSATION_REFERENCE,
            $serializedMessages,
            static::CONTEXT_WINDOW,
        );
    }

    /**
     * @return \Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAiFoundationStubMock(): AiFoundationStubInterface
    {
        return $this->createMock(AiFoundationStubInterface::class);
    }

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     */
    protected function createProviderAwareGatewayChatHistory(string $providerName, array $serializedMessages = []): GatewayChatHistory
    {
        return new GatewayChatHistory(
            $this->createAiFoundationStubMock(),
            static::CONVERSATION_REFERENCE,
            $serializedMessages,
            static::CONTEXT_WINDOW,
            new ChatHistoryReasoningFilter(),
            $providerName,
            static::MODEL_NAME,
        );
    }

    protected function createAssistantMessageWithReasoning(): AssistantMessage
    {
        return new AssistantMessage([
            new ReasoningContent(static::REASONING_CONTENT, static::REASONING_SIGNATURE),
            new TextContent(static::MESSAGE_CONTENT),
        ]);
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     *
     * @return array<int, array<string, mixed>>
     */
    protected function serializeMessages(array $messages): array
    {
        return json_decode((string)json_encode($messages), true);
    }
}
