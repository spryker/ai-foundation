<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\Tool;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;
use SprykerTest\Zed\AiFoundation\Business\NeuronAi\Transfers\AiResponseTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group NeuronAi
 * @group Facade
 * @group AiFoundationFacadeConversationHistoryTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeConversationHistoryTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_anthropic';

    protected const string TEST_ANTHROPIC_KEY = 'test-key';

    protected const string TEST_ANTHROPIC_MODEL = 'claude-3-5-sonnet-20241022';

    protected const string TEST_CONVERSATION_PREFIX = 'test-chat-history-';

    protected const string TEST_TOOL_NAME = 'test_calculator';

    protected const string TEST_TOOL_SET_NAME = 'test_tool_set';

    protected const string TEST_TOOL_RESULT = '42';

    protected AiFoundationBusinessTester $tester;

    protected ?AiFoundationFacadeInterface $facade = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facade = $this->createFacadeWithMockedProvider();
    }

    public function testGivenNewConversationWhenSendingFirstMessageThenMessageIsPersistedInDatabase(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $promptRequest = $this->createPromptRequest($conversationReference, 'Generate random string');

        // Act
        $response = $this->facade->prompt($promptRequest);

        // Assert
        $this->assertConversationExistsInDatabase($conversationReference);
        $this->assertConversationHasMessageCount($conversationReference, 2);
    }

    public function testGivenExistingConversationWhenSendingSecondMessageThenBothMessagesArePersistedInDatabase(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'First message'));

        // Act
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Second message'));

        // Assert
        $this->assertConversationHasMessageCount($conversationReference, 4);
    }

    public function testGivenConversationWithMessagesWhenRetrievingHistoryThenAllMessagesAreReturned(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'First message'));
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Second message'));

        // Act
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);

        // Assert
        $this->assertSame(1, $conversationHistoryCollection->getConversationHistories()->count());
        $this->assertSame(4, $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages()->count());
    }

    public function testGivenMultipleMessagesWhenSendingToConversationThenAllMessagesArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'First message'));

        // Act
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Second message'));

        // Assert
        $this->assertConversationHasMessageCount($conversationReference, 4);
    }

    public function testGivenConversationReferenceWhenSendingThreeMessagesThenDatabaseContainsSixMessages(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();

        // Act
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Message 1'));
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Message 2'));
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Message 3'));

        // Assert
        $this->assertConversationHasMessageCount($conversationReference, 6);
    }

    public function testGivenNewConversationWhenSendingMessageThenConversationIsCreated(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();

        // Act
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Test message'));

        // Assert
        $this->assertConversationExistsInDatabase($conversationReference);
        $this->assertConversationHasMessageCount($conversationReference, 2);
    }

    public function testGivenConversationWithMessagesWhenRetrievingHistoryThenMessagesContainUserAndAssistantRoles(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $this->facade->prompt($this->createPromptRequest($conversationReference, 'Hello AI'));

        // Act
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $this->assertSame('user', $messages->offsetGet(0)->getType());
        $this->assertSame('assistant', $messages->offsetGet(1)->getType());
    }

    public function testGivenNonExistentConversationReferenceWhenRetrievingHistoryThenEmptyCollectionIsReturned(): void
    {
        // Arrange
        $conversationReference = 'non-existent-conversation-reference';

        // Act
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);

        // Assert
        $conversationHistory = $conversationHistoryCollection->getConversationHistories()->offsetGet(0);
        $this->assertSame(0, $conversationHistory->getMessages()->count());
    }

    public function testGivenMultipleConversationsWhenSendingMessagesThenEachConversationIsStoredSeparately(): void
    {
        // Arrange
        $conversationReference1 = $this->generateUniqueConversationReference();
        $conversationReference2 = $this->generateUniqueConversationReference();

        // Act
        $this->facade->prompt($this->createPromptRequest($conversationReference1, 'Conversation 1'));
        $this->facade->prompt($this->createPromptRequest($conversationReference2, 'Conversation 2'));

        // Assert
        $this->assertConversationHasMessageCount($conversationReference1, 2);
        $this->assertConversationHasMessageCount($conversationReference2, 2);
    }

    public function testGivenConversationWithMessagesWhenRetrievingHistoryThenMessageContentIsPreserved(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $expectedUserMessage = 'What is the weather today?';
        $this->facade->prompt($this->createPromptRequest($conversationReference, $expectedUserMessage));

        // Act
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $this->assertStringContainsString($expectedUserMessage, $messages->offsetGet(0)->getContent());
    }

    public function testGivenConversationWithToolCallWhenRetrievingHistoryThenToolCallMessagesArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCall();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Execute calculator tool');

        // Act
        $facade->prompt($promptRequest);

        // Assert
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $this->assertGreaterThan(2, $messages->count());
    }

    public function testGivenConversationWithMultipleToolCallsWhenRetrievingHistoryThenAllToolCallMessagesArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithMultipleToolCalls();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Execute tools multiple times');

        // Act
        $facade->prompt($promptRequest);

        // Assert
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $this->assertGreaterThan(2, $messages->count());
    }

    public function testGivenConversationWithToolCallAndArgumentsWhenRetrievingHistoryThenArgumentsArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $expectedArguments = ['number1' => 10, 'number2' => 32];
        $facade = $this->createFacadeWithToolCallAndArguments($expectedArguments);
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Calculate with arguments');

        // Act
        $facade->prompt($promptRequest);

        // Assert
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);
        $this->assertGreaterThan(2, $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages()->count());
    }

    public function testGivenMultipleMessagesWithToolCallsWhenSendingToConversationThenAllMessagesIncludingToolCallsArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCallAndFollowUp();
        $promptRequestWithTool = $this->createPromptRequestWithTool($conversationReference, 'First message with tool');
        $promptRequestWithoutTool = $this->createPromptRequestWithoutTool($conversationReference, 'Second message without tool');

        // Act
        $facade->prompt($promptRequestWithTool);
        $facade->prompt($promptRequestWithoutTool);

        // Assert
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);
        $this->assertGreaterThan(4, $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages()->count());
    }

    public function testGivenConversationWithToolCallWhenRetrievingHistoryThenToolResultsArePersisted(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCall();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Execute tool and check result');

        // Act
        $facade->prompt($promptRequest);

        // Assert
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $this->assertGreaterThan(2, $messages->count());
    }

    public function testGivenConversationWithToolCallWhenRetrievingHistoryThenToolCallMessageHasCorrectTypeAndToolData(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCall();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Execute calculator tool');

        // Act
        $facade->prompt($promptRequest);
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $toolCallMessage = $messages->offsetGet(1);

        $this->assertSame(AiFoundationConstants::MESSAGE_TYPE_TOOL_CALL, $toolCallMessage->getType());
        $this->assertGreaterThan(0, $toolCallMessage->getToolInvocations()->count());
        $this->assertSame(static::TEST_TOOL_NAME, $toolCallMessage->getToolInvocations()->offsetGet(0)->getName());
    }

    public function testGivenConversationWithToolCallWhenRetrievingHistoryThenToolResultMessageHasCorrectTypeAndToolResult(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCall();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Execute calculator tool');

        // Act
        $facade->prompt($promptRequest);
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $toolResultMessage = $messages->offsetGet(2);

        $this->assertSame(AiFoundationConstants::MESSAGE_TYPE_TOOL_RESULT, $toolResultMessage->getType());
        $this->assertGreaterThan(0, $toolResultMessage->getToolInvocations()->count());
        $this->assertSame(static::TEST_TOOL_RESULT, $toolResultMessage->getToolInvocations()->offsetGet(0)->getResult());
    }

    public function testGivenConversationWithToolCallArgumentsWhenRetrievingHistoryThenToolCallMessagePreservesArguments(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $expectedArguments = ['number1' => 10, 'number2' => 32];
        $facade = $this->createFacadeWithToolCallAndArguments($expectedArguments);
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Calculate with arguments');

        // Act
        $facade->prompt($promptRequest);
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $toolCallMessage = $messages->offsetGet(1);

        $this->assertSame(AiFoundationConstants::MESSAGE_TYPE_TOOL_CALL, $toolCallMessage->getType());
        $toolInvocation = $toolCallMessage->getToolInvocations()->offsetGet(0);
        $this->assertSame($expectedArguments, $toolInvocation->getArguments());
    }

    public function testGivenToolCallMessageCarriesReasoningAndTextWhenRetrievingHistoryThenReasoningAndContentArePopulatedSeparately(): void
    {
        // Arrange
        $conversationReference = $this->generateUniqueConversationReference();
        $facade = $this->createFacadeWithToolCallCarryingReasoning();
        $promptRequest = $this->createPromptRequestWithTool($conversationReference, 'Run calculator');

        // Act
        $facade->prompt($promptRequest);
        $conversationHistoryCollection = $this->retrieveConversationHistoryFromFacade($facade, $conversationReference);

        // Assert
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $toolCallMessage = $messages->offsetGet(1);

        $this->assertSame(AiFoundationConstants::MESSAGE_TYPE_TOOL_CALL, $toolCallMessage->getType());
        $this->assertSame('I will use the calculator now.', $toolCallMessage->getContent());
        $this->assertSame('User wants arithmetic; calculator tool is the right choice.', $toolCallMessage->getReasoning());
        $this->assertGreaterThan(0, $toolCallMessage->getToolInvocations()->count());
    }

    protected function retrieveConversationHistoryFromFacade(
        AiFoundationFacadeInterface $facade,
        string $conversationReference,
    ): ConversationHistoryCollectionTransfer {
        return $facade->getConversationHistoryCollection(
            (new ConversationHistoryCriteriaTransfer())->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())->setConversationReferences([$conversationReference]),
            ),
        );
    }

    protected function createFacadeWithMockedProvider(): AiFoundationFacadeInterface
    {
        $mockProvider = $this->createMockProvider();
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->tester->getFactory()->createNeuronAiMessageMapper(),
            toolMapper: $this->tester->getFactory()->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->tester->getFactory()->createNeuronAiChatHistoryResolver(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: [],
            postPromptPlugins: [],
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $mockVendorProviderPlugin,
        );

        return $this->tester->getFacade();
    }

    protected function createMockProvider(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $mockProvider->method('chat')
            ->willReturn(new AssistantMessage('Test response from AI'));

        $mockProvider->method('structured')
            ->willReturn(new AssistantMessage(json_encode([
                'rand_string' => 'test-' . uniqid(),
                'any_object' => ['key' => 'value'],
                'array_of_strings' => ['test'],
                'ai_response_paths' => [['path' => '/test/path.php']],
            ])));

        return $mockProvider;
    }

    protected function createMockConfig(): AiFoundationConfig
    {
        $config = $this->createMock(AiFoundationConfig::class);

        $config->method('getAiConfigurations')
            ->willReturn([
                static::TEST_AI_ENGINE => [
                    'provider_name' => AiFoundationConstants::PROVIDER_ANTHROPIC,
                    'provider_config' => [
                        'key' => static::TEST_ANTHROPIC_KEY,
                        'model' => static::TEST_ANTHROPIC_MODEL,
                        'parameters' => [],
                    ],
                ],
            ]);

        $config->method('getConversationHistoryContextWindow')->willReturn(10);

        return $config;
    }

    protected function createPromptRequest(string $conversationReference, string $message): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setConversationReference($conversationReference)
            ->setPromptMessage((new PromptMessageTransfer())->setContent($message))
            ->setStructuredMessage(new AiResponseTransfer())
            ->setMaxRetries(1);
    }

    protected function retrieveConversationHistory(string $conversationReference): ConversationHistoryCollectionTransfer
    {
        $criteria = (new ConversationHistoryCriteriaTransfer())
            ->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())->setConversationReferences([$conversationReference]),
            );

        return $this->facade->getConversationHistoryCollection($criteria);
    }

    protected function generateUniqueConversationReference(): string
    {
        return static::TEST_CONVERSATION_PREFIX . uniqid('', true);
    }

    protected function assertConversationExistsInDatabase(string $conversationReference): void
    {
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();

        $this->assertGreaterThan(
            0,
            $messages->count(),
            sprintf('Conversation with reference "%s" should exist in database', $conversationReference),
        );
    }

    protected function assertConversationHasMessageCount(string $conversationReference, int $expectedCount): void
    {
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();
        $actualCount = $messages->count();

        $this->assertSame(
            $expectedCount,
            $actualCount,
            sprintf('Conversation "%s" should have %d messages, but has %d', $conversationReference, $expectedCount, $actualCount),
        );
    }

    protected function createTestTool(): Tool
    {
        $tool = new Tool(
            name: static::TEST_TOOL_NAME,
            description: 'A test calculator tool that adds two numbers',
        );

        $tool->setCallable(function (): string {
            return static::TEST_TOOL_RESULT;
        });

        return $tool;
    }

    protected function createFacadeWithToolCall(): AiFoundationFacadeInterface
    {
        $testTool = $this->createTestTool();
        $mockProvider = $this->createMockProviderWithToolCall($testTool);

        return $this->createFacadeWithMockedProviderAndTools($mockProvider, [$testTool]);
    }

    protected function createFacadeWithMultipleToolCalls(): AiFoundationFacadeInterface
    {
        $testTool = $this->createTestTool();
        $mockProvider = $this->createMockProviderWithMultipleToolInvocations($testTool);

        return $this->createFacadeWithMockedProviderAndTools($mockProvider, [$testTool]);
    }

    /**
     * @param array<string, int> $arguments
     */
    protected function createFacadeWithToolCallAndArguments(array $arguments): AiFoundationFacadeInterface
    {
        $testTool = $this->createTestTool();
        $mockProvider = $this->createMockProviderWithToolCallAndArguments($testTool, $arguments);

        return $this->createFacadeWithMockedProviderAndTools($mockProvider, [$testTool]);
    }

    protected function createFacadeWithToolCallAndFollowUp(): AiFoundationFacadeInterface
    {
        $testTool = $this->createTestTool();
        $mockProvider = $this->createMockProviderWithToolCallAndFollowUp($testTool);

        return $this->createFacadeWithMockedProviderAndTools($mockProvider, [$testTool]);
    }

    protected function createFacadeWithToolCallCarryingReasoning(): AiFoundationFacadeInterface
    {
        $testTool = $this->createTestTool();
        $mockProvider = $this->createMockProviderWithToolCallCarryingReasoning($testTool);

        return $this->createFacadeWithMockedProviderAndTools($mockProvider, [$testTool]);
    }

    /**
     * @param array<\NeuronAI\Tools\Tool> $tools
     */
    protected function createFacadeWithMockedProviderAndTools(
        AIProviderInterface $mockProvider,
        array $tools,
    ): AiFoundationFacadeInterface {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->tester->getFactory()->createNeuronAiMessageMapper(),
            toolMapper: $this->tester->getFactory()->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->tester->getFactory()->createNeuronAiChatHistoryResolver(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: $this->convertToolsToToolSets($tools),
            postPromptPlugins: [],
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $mockVendorProviderPlugin,
        );

        return $this->tester->getFacade();
    }

    protected function createMockProviderWithToolCall(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage = new ToolCallMessage('Calling tool', [$tool]);
        $finalMessage = new AssistantMessage('Tool executed successfully');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage,
                $finalMessage,
            );

        return $mockProvider;
    }

    protected function createMockProviderWithToolCallCarryingReasoning(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage = new ToolCallMessage(
            [
                new ReasoningContent('User wants arithmetic; calculator tool is the right choice.'),
                new TextContent('I will use the calculator now.'),
            ],
            [$tool],
        );
        $finalMessage = new AssistantMessage('Tool executed successfully');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage,
                $finalMessage,
            );

        return $mockProvider;
    }

    protected function createMockProviderWithMultipleToolInvocations(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage1 = new ToolCallMessage('Calling tool first time', [$tool]);
        $toolCallMessage2 = new ToolCallMessage('Calling tool second time', [$tool]);
        $finalMessage = new AssistantMessage('All tools executed successfully');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage1,
                $toolCallMessage2,
                $finalMessage,
            );

        return $mockProvider;
    }

    /**
     * @param array<string, int> $arguments
     */
    protected function createMockProviderWithToolCallAndArguments(Tool $tool, array $arguments): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $tool->setInputs($arguments);

        $toolCallMessage = new ToolCallMessage('Calling tool with arguments', [$tool]);
        $finalMessage = new AssistantMessage('Tool executed with arguments');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage,
                $finalMessage,
            );

        return $mockProvider;
    }

    protected function createMockProviderWithToolCallAndFollowUp(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage = new ToolCallMessage('Calling tool', [$tool]);
        $finalMessageAfterTool = new AssistantMessage('Tool executed successfully');
        $followUpMessage = new AssistantMessage('Follow-up message without tool');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage,
                $finalMessageAfterTool,
                $followUpMessage,
            );

        return $mockProvider;
    }

    protected function createPromptRequestWithTool(string $conversationReference, string $message): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setConversationReference($conversationReference)
            ->setPromptMessage((new PromptMessageTransfer())->setContent($message))
            ->addToolSetName(static::TEST_TOOL_SET_NAME);
    }

    protected function createPromptRequestWithoutTool(string $conversationReference, string $message): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setConversationReference($conversationReference)
            ->setPromptMessage((new PromptMessageTransfer())->setContent($message));
    }

    /**
     * @param array<\NeuronAI\Tools\Tool> $tools
     *
     * @return array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
     */
    protected function convertToolsToToolSets(array $tools): array
    {
        if (count($tools) === 0) {
            return [];
        }

        $toolPlugins = [];

        foreach ($tools as $tool) {
            $tool->execute();

            $plugin = $this->createMock(ToolPluginInterface::class);
            $plugin->method('getName')->willReturn($tool->getName());
            $plugin->method('getDescription')->willReturn($tool->getDescription());
            $plugin->method('getParameters')->willReturn([]);
            $plugin->method('execute')->willReturn($tool->getResult());

            $toolPlugins[] = $plugin;
        }

        $toolSetPlugin = $this->createMock(ToolSetPluginInterface::class);
        $toolSetPlugin->method('getName')->willReturn(static::TEST_TOOL_SET_NAME);
        $toolSetPlugin->method('getTools')->willReturn($toolPlugins);

        return [$toolSetPlugin];
    }

    protected function assertConversationHasToolCallMessages(string $conversationReference): void
    {
        $conversationHistoryCollection = $this->retrieveConversationHistory($conversationReference);
        $messages = $conversationHistoryCollection->getConversationHistories()->offsetGet(0)->getMessages();

        $this->assertGreaterThan(
            2,
            $messages->count(),
            sprintf('Conversation with reference "%s" should contain tool call messages', $conversationReference),
        );
    }
}
