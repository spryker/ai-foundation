<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiConfigurationTransfer;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\UserMessage;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactory;
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
 * @group GatewayChatHistoryFactoryTest
 * Add your own group annotations below this line
 */
class GatewayChatHistoryFactoryTest extends Unit
{
    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const string MESSAGE_CONTENT = 'Restored message';

    protected const string PROVIDER_NAME = 'bedrock';

    protected const string PROVIDER_NAME_OTHER = 'openai_responses';

    protected const string MODEL_NAME = 'test-model';

    protected const string MODEL_NAME_OTHER = 'other-test-model';

    protected const string PROVIDER_CONFIG_KEY_MODEL = 'model';

    protected const string REASONING_CONTENT = 'Thinking about the answer';

    protected const string REASONING_SIGNATURE = 'reasoning-signature';

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

    public function testGivenReasoningOfConfiguredProviderAndModelWhenCreatingGatewayChatHistoryThenReasoningIsKept(): void
    {
        // Arrange
        $chatSessionResponseTransfer = $this->createChatSessionResponseTransferWithAiConfiguration(
            $this->createOriginalMessagesWithReasoning(static::PROVIDER_NAME, static::MODEL_NAME),
            static::PROVIDER_NAME,
            static::MODEL_NAME,
        );

        // Act
        $gatewayChatHistory = $this->createFactoryWithReasoningFilter()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $chatSessionResponseTransfer,
        );

        // Assert
        $this->assertSame(static::REASONING_SIGNATURE, $gatewayChatHistory->getMessages()[1]->getReasoning()?->id);
    }

    /**
     * @dataProvider provideForeignOrigins
     */
    public function testGivenReasoningOfAnotherOriginWhenCreatingGatewayChatHistoryThenReasoningIsRemoved(
        string $originProviderName,
        string $originModelName
    ): void {
        // Arrange
        $chatSessionResponseTransfer = $this->createChatSessionResponseTransferWithAiConfiguration(
            $this->createOriginalMessagesWithReasoning($originProviderName, $originModelName),
            static::PROVIDER_NAME,
            static::MODEL_NAME,
        );

        // Act
        $gatewayChatHistory = $this->createFactoryWithReasoningFilter()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $chatSessionResponseTransfer,
        );

        // Assert
        $this->assertNull($gatewayChatHistory->getMessages()[1]->getReasoning());
        $this->assertSame(static::MESSAGE_CONTENT, $gatewayChatHistory->getMessages()[1]->getContent());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideForeignOrigins(): array
    {
        return [
            'another provider' => [static::PROVIDER_NAME_OTHER, static::MODEL_NAME],
            'same provider with another model' => [static::PROVIDER_NAME, static::MODEL_NAME_OTHER],
        ];
    }

    public function testGivenNoAiConfigurationWhenCreatingGatewayChatHistoryThenReasoningIsKept(): void
    {
        // Arrange
        $chatSessionResponseTransfer = $this->createChatSessionResponseTransfer(
            $this->createOriginalMessagesWithReasoning(static::PROVIDER_NAME_OTHER, static::MODEL_NAME_OTHER),
        );

        // Act
        $gatewayChatHistory = $this->createFactoryWithReasoningFilter()->createGatewayChatHistory(
            $this->createChatSessionRequestTransfer(),
            $chatSessionResponseTransfer,
        );

        // Assert
        $this->assertSame(static::REASONING_SIGNATURE, $gatewayChatHistory->getMessages()[1]->getReasoning()?->id);
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

    protected function createChatSessionResponseTransferWithAiConfiguration(
        string $originalMessages,
        string $providerName,
        string $modelName
    ): ChatSessionResponseTransfer {
        return $this->createChatSessionResponseTransfer($originalMessages)
            ->setAiConfiguration(
                (new AiConfigurationTransfer())
                    ->setProviderName($providerName)
                    ->setProviderConfig([static::PROVIDER_CONFIG_KEY_MODEL => $modelName]),
            );
    }

    protected function createOriginalMessagesWithReasoning(string $providerName, string $modelName): string
    {
        $assistantMessage = (new ChatHistoryReasoningFilter())->markMessageOrigin(
            new AssistantMessage([
                new ReasoningContent(static::REASONING_CONTENT, static::REASONING_SIGNATURE),
                new TextContent(static::MESSAGE_CONTENT),
            ]),
            $providerName,
            $modelName,
        );

        return (string)json_encode([
            (new UserMessage(static::MESSAGE_CONTENT))->jsonSerialize(),
            $assistantMessage->jsonSerialize(),
        ]);
    }

    protected function createFactory(): GatewayChatHistoryFactory
    {
        return new GatewayChatHistoryFactory($this->createMock(AiFoundationStubInterface::class));
    }

    protected function createFactoryWithReasoningFilter(): GatewayChatHistoryFactory
    {
        return new GatewayChatHistoryFactory(
            $this->createMock(AiFoundationStubInterface::class),
            new ChatHistoryReasoningFilter(),
        );
    }
}
