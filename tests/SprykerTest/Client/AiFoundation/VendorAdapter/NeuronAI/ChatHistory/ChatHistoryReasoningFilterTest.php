<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Codeception\Test\Unit;
use NeuronAI\Chat\Enums\ContentBlockType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Anthropic\MessageMapper as AnthropicMessageMapper;
use NeuronAI\Providers\AWS\MessageMapper as AwsMessageMapper;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Tools\Tool;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistory;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\ChatHistoryReasoningFilter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group VendorAdapter
 * @group NeuronAI
 * @group ChatHistory
 * @group ChatHistoryReasoningFilterTest
 * Add your own group annotations below this line
 */
class ChatHistoryReasoningFilterTest extends Unit
{
    protected const string PROVIDER_NAME_BEDROCK = 'bedrock';

    protected const string PROVIDER_NAME_OPENAI_RESPONSES = 'openai_responses';

    protected const string MODEL_NAME = 'test-model';

    protected const string MODEL_NAME_OTHER = 'other-test-model';

    protected const string MESSAGE_CONTENT = 'Hello there';

    protected const string REASONING_CONTENT = 'Thinking about the answer';

    protected const string REASONING_SIGNATURE = 'reasoning-signature';

    protected const string REASONING_METADATA_VALUE = 'provider-reasoning-value';

    protected const string REDACTED_REASONING_DATA = 'cmVkYWN0ZWQtcmVhc29uaW5n';

    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const int CONTEXT_WINDOW = 50000;

    protected const string TOOL_NAME = 'test_calculator';

    protected const string TOOL_DESCRIPTION = 'A test calculator tool.';

    protected const string TOOL_CALL_ID = 'tool-call-id';

    protected const array TOOL_INPUTS = ['number' => 42];

    protected const string METADATA_KEY_PROVIDER_NAME = 'ai_provider_name';

    protected const string METADATA_KEY_MODEL_NAME = 'ai_model_name';

    protected const string METADATA_KEY_AWS_REDACTED_REASONING = 'aws_redacted_reasoning';

    protected const string METADATA_KEY_AWS_TOOL_POSITIONS = 'aws_tool_positions';

    protected const string METADATA_KEY_ANTHROPIC_REDACTED_THINKING = 'anthropic_redacted_thinking';

    protected const string METADATA_KEY_ANTHROPIC_TOOL_POSITIONS = 'anthropic_tool_positions';

    protected const string METADATA_KEY_THOUGHT_SIGNATURE = 'thought_signature';

    protected const string METADATA_KEY_REASONING_CONTENT = 'reasoning_content';

    protected const string MESSAGE_KEY_CONTENT = 'content';

    protected const string CONTENT_BLOCK_KEY_META = 'meta';

    protected const string MAPPED_CONTENT_KEY_TYPE = 'type';

    protected const string ANTHROPIC_TYPE_THINKING = 'thinking';

    protected const string ANTHROPIC_TYPE_REDACTED_THINKING = 'redacted_thinking';

    protected const string ANTHROPIC_TYPE_TEXT = 'text';

    protected const string ANTHROPIC_TYPE_TOOL_USE = 'tool_use';

    protected const string AWS_TYPE_REASONING = 'reasoningContent';

    protected const string AWS_TYPE_TEXT = 'text';

    protected const string AWS_TYPE_TOOL_USE = 'toolUse';

    public function testGivenReasoningOfAnotherProviderWhenRemovingForeignReasoningThenReasoningIsRemoved(): void
    {
        // Arrange
        $serializedMessages = $this->createSerializedMarkedAssistantMessages(static::PROVIDER_NAME_OPENAI_RESPONSES, static::MODEL_NAME);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame([ContentBlockType::TEXT->value], $this->getContentBlockTypes($filteredMessages[0]));
    }

    public function testGivenReasoningOfAnotherModelWhenRemovingForeignReasoningThenReasoningIsRemoved(): void
    {
        // Arrange
        $serializedMessages = $this->createSerializedMarkedAssistantMessages(static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME_OTHER);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame([ContentBlockType::TEXT->value], $this->getContentBlockTypes($filteredMessages[0]));
    }

    public function testGivenReasoningWithoutOriginMarkWhenRemovingForeignReasoningThenReasoningIsRemoved(): void
    {
        // Arrange
        $serializedMessages = $this->serializeMessages([$this->createAssistantMessageWithReasoning()]);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame([ContentBlockType::TEXT->value], $this->getContentBlockTypes($filteredMessages[0]));
    }

    public function testGivenReasoningOfSameProviderAndModelWhenRemovingForeignReasoningThenMessageIsKeptUnchanged(): void
    {
        // Arrange
        $serializedMessages = $this->createSerializedMarkedAssistantMessages(static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame($serializedMessages, $filteredMessages);
    }

    public function testGivenForeignMessagesWhenRemovingForeignReasoningThenNonReasoningContentIsKept(): void
    {
        // Arrange
        $serializedMessages = $this->serializeMessages([new UserMessage(static::MESSAGE_CONTENT), $this->createAssistantMessageWithReasoning()]);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame($serializedMessages[0], $filteredMessages[0]);
        $this->assertSame(static::MESSAGE_CONTENT, $filteredMessages[1][static::MESSAGE_KEY_CONTENT][0][static::MESSAGE_KEY_CONTENT]);
    }

    /**
     * @dataProvider provideForeignOrigins
     */
    public function testGivenForeignMessageWithProviderReasoningMetadataWhenRemovingForeignReasoningThenMetadataIsRemoved(
        ?string $providerName,
        ?string $modelName
    ): void {
        // Arrange
        $serializedMessages = $this->serializeMessages([
            $this->markMessageOrigin($this->createAssistantMessageWithReasoningMetadata(), $providerName, $modelName),
        ]);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        foreach ($this->getProviderReasoningMetadataKeys() as $metadataKey) {
            $this->assertArrayNotHasKey($metadataKey, $filteredMessages[0]);
            $this->assertArrayNotHasKey($metadataKey, $filteredMessages[0][static::MESSAGE_KEY_CONTENT][0][static::CONTENT_BLOCK_KEY_META]);
        }
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public function provideForeignOrigins(): array
    {
        return [
            'another provider' => [static::PROVIDER_NAME_OPENAI_RESPONSES, static::MODEL_NAME],
            'same provider with another model' => [static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME_OTHER],
            'no origin mark' => [null, null],
        ];
    }

    public function testGivenSameOriginMessageWithProviderReasoningMetadataWhenRemovingForeignReasoningThenMetadataIsKept(): void
    {
        // Arrange
        $serializedMessages = $this->serializeMessages([
            $this->markMessageOrigin($this->createAssistantMessageWithReasoningMetadata(), static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME),
        ]);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame($serializedMessages, $filteredMessages);
        $this->assertArrayHasKey(static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING, $filteredMessages[0]);
        $this->assertArrayHasKey(static::METADATA_KEY_THOUGHT_SIGNATURE, $filteredMessages[0][static::MESSAGE_KEY_CONTENT][0][static::CONTENT_BLOCK_KEY_META]);
    }

    /**
     * @dataProvider provideToolCallLayouts
     *
     * @param array<int, string> $redactedReasoning
     * @param array<int, int> $toolPositions
     * @param array<int, string> $expectedSameOriginContentTypes
     * @param array<int, string> $expectedForeignContentTypes
     */
    public function testGivenForeignToolCallMessageWhenRemovingForeignReasoningThenProviderMapsToolCallsInOriginalOrder(
        MessageMapperInterface $messageMapper,
        string $redactedReasoningMetadataKey,
        string $toolPositionsMetadataKey,
        array $redactedReasoning,
        array $toolPositions,
        array $expectedSameOriginContentTypes,
        array $expectedForeignContentTypes
    ): void {
        // Arrange
        $toolCallMessage = $this->createToolCallMessageWithReasoning();
        $toolCallMessage->addMetadata($redactedReasoningMetadataKey, $redactedReasoning);
        $toolCallMessage->addMetadata($toolPositionsMetadataKey, $toolPositions);
        $serializedMessages = $this->serializeMessages([
            $this->markMessageOrigin($toolCallMessage, static::PROVIDER_NAME_OPENAI_RESPONSES, static::MODEL_NAME),
        ]);

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning($serializedMessages, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame($expectedSameOriginContentTypes, $this->getMappedContentTypes($messageMapper, $serializedMessages));
        $this->assertSame($expectedForeignContentTypes, $this->getMappedContentTypes($messageMapper, $filteredMessages));
        $this->assertArrayNotHasKey($redactedReasoningMetadataKey, $filteredMessages[0]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function provideToolCallLayouts(): array
    {
        return [
            'anthropic with redacted thinking before the text' => [
                new AnthropicMessageMapper(),
                static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING,
                static::METADATA_KEY_ANTHROPIC_TOOL_POSITIONS,
                [1 => static::REDACTED_REASONING_DATA],
                [3],
                [static::ANTHROPIC_TYPE_THINKING, static::ANTHROPIC_TYPE_REDACTED_THINKING, static::ANTHROPIC_TYPE_TEXT, static::ANTHROPIC_TYPE_TOOL_USE],
                [static::ANTHROPIC_TYPE_TEXT, static::ANTHROPIC_TYPE_TOOL_USE],
            ],
            'anthropic with tool call before the text' => [
                new AnthropicMessageMapper(),
                static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING,
                static::METADATA_KEY_ANTHROPIC_TOOL_POSITIONS,
                [],
                [1],
                [static::ANTHROPIC_TYPE_THINKING, static::ANTHROPIC_TYPE_TOOL_USE, static::ANTHROPIC_TYPE_TEXT],
                [static::ANTHROPIC_TYPE_TOOL_USE, static::ANTHROPIC_TYPE_TEXT],
            ],
            'aws with redacted reasoning before the text' => [
                new AwsMessageMapper(),
                static::METADATA_KEY_AWS_REDACTED_REASONING,
                static::METADATA_KEY_AWS_TOOL_POSITIONS,
                [1 => static::REDACTED_REASONING_DATA],
                [3],
                [static::AWS_TYPE_REASONING, static::AWS_TYPE_REASONING, static::AWS_TYPE_TEXT, static::AWS_TYPE_TOOL_USE],
                [static::AWS_TYPE_TEXT, static::AWS_TYPE_TOOL_USE],
            ],
            'aws with tool call before the text' => [
                new AwsMessageMapper(),
                static::METADATA_KEY_AWS_REDACTED_REASONING,
                static::METADATA_KEY_AWS_TOOL_POSITIONS,
                [],
                [1],
                [static::AWS_TYPE_REASONING, static::AWS_TYPE_TOOL_USE, static::AWS_TYPE_TEXT],
                [static::AWS_TYPE_TOOL_USE, static::AWS_TYPE_TEXT],
            ],
        ];
    }

    public function testGivenSameOriginToolCallMessageWhenRestoringHistoryThenProviderReceivesSignedReasoningUnchanged(): void
    {
        // Arrange
        $toolCallMessage = $this->createToolCallMessageWithReasoning();
        $toolCallMessage->addMetadata(static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING, [1 => static::REDACTED_REASONING_DATA]);
        $toolCallMessage->addMetadata(static::METADATA_KEY_ANTHROPIC_TOOL_POSITIONS, [3]);
        $this->markMessageOrigin($toolCallMessage, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);
        $messageMapper = new AnthropicMessageMapper();

        // Act
        $filteredMessages = (new ChatHistoryReasoningFilter())->removeForeignReasoning(
            $this->serializeMessages([$toolCallMessage]),
            static::PROVIDER_NAME_BEDROCK,
            static::MODEL_NAME,
        );

        // Assert
        $restoredMessages = $this->deserializeMessages($filteredMessages);
        $this->assertInstanceOf(ToolCallMessage::class, $restoredMessages[0]);
        $this->assertSame(static::REASONING_SIGNATURE, $restoredMessages[0]->getReasoning()?->id);
        $this->assertEquals($messageMapper->map([$toolCallMessage]), $messageMapper->map($restoredMessages));
    }

    public function testGivenToolCallMessageWhenMarkingMessageOriginThenProviderAndModelAreStored(): void
    {
        // Arrange
        $toolCallMessage = $this->createToolCallMessageWithReasoning();

        // Act
        (new ChatHistoryReasoningFilter())->markMessageOrigin($toolCallMessage, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertSame(static::PROVIDER_NAME_BEDROCK, $toolCallMessage->getMetadata(static::METADATA_KEY_PROVIDER_NAME));
        $this->assertSame(static::MODEL_NAME, $toolCallMessage->getMetadata(static::METADATA_KEY_MODEL_NAME));
    }

    public function testGivenUserMessageWhenMarkingMessageOriginThenMessageIsNotMarked(): void
    {
        // Arrange
        $userMessage = new UserMessage(static::MESSAGE_CONTENT);

        // Act
        (new ChatHistoryReasoningFilter())->markMessageOrigin($userMessage, static::PROVIDER_NAME_BEDROCK, static::MODEL_NAME);

        // Assert
        $this->assertNull($userMessage->getMetadata(static::METADATA_KEY_PROVIDER_NAME));
        $this->assertNull($userMessage->getMetadata(static::METADATA_KEY_MODEL_NAME));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function createSerializedMarkedAssistantMessages(string $providerName, string $modelName): array
    {
        $message = (new ChatHistoryReasoningFilter())->markMessageOrigin($this->createAssistantMessageWithReasoning(), $providerName, $modelName);

        return $this->serializeMessages([$message]);
    }

    protected function markMessageOrigin(Message $message, ?string $providerName, ?string $modelName): Message
    {
        if ($providerName === null) {
            return $message;
        }

        return (new ChatHistoryReasoningFilter())->markMessageOrigin($message, $providerName, $modelName);
    }

    protected function createAssistantMessageWithReasoning(): AssistantMessage
    {
        return new AssistantMessage([
            new ReasoningContent(static::REASONING_CONTENT, static::REASONING_SIGNATURE),
            new TextContent(static::MESSAGE_CONTENT),
        ]);
    }

    protected function createAssistantMessageWithReasoningMetadata(): AssistantMessage
    {
        $textContent = new TextContent(static::MESSAGE_CONTENT);
        $textContent->addMetadata(static::METADATA_KEY_THOUGHT_SIGNATURE, static::REASONING_METADATA_VALUE);

        $assistantMessage = new AssistantMessage([$textContent]);

        foreach ($this->getProviderReasoningMetadataKeys() as $metadataKey) {
            $assistantMessage->addMetadata($metadataKey, static::REASONING_METADATA_VALUE);
        }

        return $assistantMessage;
    }

    protected function createToolCallMessageWithReasoning(): ToolCallMessage
    {
        $tool = Tool::make(static::TOOL_NAME, static::TOOL_DESCRIPTION)
            ->setInputs(static::TOOL_INPUTS)
            ->setCallId(static::TOOL_CALL_ID);

        return new ToolCallMessage(
            [
                new ReasoningContent(static::REASONING_CONTENT, static::REASONING_SIGNATURE),
                new TextContent(static::MESSAGE_CONTENT),
            ],
            [$tool],
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getProviderReasoningMetadataKeys(): array
    {
        return [
            static::METADATA_KEY_AWS_REDACTED_REASONING,
            static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING,
            static::METADATA_KEY_THOUGHT_SIGNATURE,
            static::METADATA_KEY_REASONING_CONTENT,
        ];
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     *
     * @return array<int, array<string, mixed>>
     */
    protected function serializeMessages(array $messages): array
    {
        return json_decode((string)json_encode(array_map(static fn (Message $message): array => $message->jsonSerialize(), $messages)), true);
    }

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     *
     * @return array<\NeuronAI\Chat\Messages\Message>
     */
    protected function deserializeMessages(array $serializedMessages): array
    {
        return (new GatewayChatHistory(
            $this->createMock(AiFoundationStubInterface::class),
            static::CONVERSATION_REFERENCE,
            $serializedMessages,
            static::CONTEXT_WINDOW,
        ))->getMessages();
    }

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     *
     * @return array<int, string>
     */
    protected function getMappedContentTypes(MessageMapperInterface $messageMapper, array $serializedMessages): array
    {
        $mappedMessages = $messageMapper->map($this->deserializeMessages($serializedMessages));

        return array_map(
            static fn (array $mappedContent): string => (string)($mappedContent[static::MAPPED_CONTENT_KEY_TYPE] ?? array_key_first($mappedContent)),
            $mappedMessages[0][static::MESSAGE_KEY_CONTENT],
        );
    }

    /**
     * @param array<string, mixed> $serializedMessage
     *
     * @return list<string>
     */
    protected function getContentBlockTypes(array $serializedMessage): array
    {
        return array_column($serializedMessage[static::MESSAGE_KEY_CONTENT], 'type');
    }
}
