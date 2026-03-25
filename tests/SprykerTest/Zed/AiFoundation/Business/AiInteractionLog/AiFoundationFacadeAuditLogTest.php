<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\AiInteractionLog;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\Tool;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLog;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Communication\Plugin\AuditLogPostPromptPlugin;
use Spryker\Zed\AiFoundation\Communication\Plugin\AuditLogPostToolCallPlugin;
use Spryker\Zed\AiFoundation\Communication\Plugin\Log\AiInteractionHandlerPlugin;
use Spryker\Zed\AiFoundation\Dependency\Plugin\PostPromptPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiInteractionLog
 * @group Facade
 * @group AiFoundationFacadeAuditLogTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeAuditLogTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_ollama';

    protected const string TEST_OLLAMA_URL = 'http://localhost:11434/api';

    protected const string TEST_OLLAMA_MODEL = 'llama3.2';

    protected const string TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    protected const string TEST_USER_MESSAGE = 'Hello, AI!';

    protected const string TEST_ASSISTANT_RESPONSE = 'Hello! How can I help you today?';

    protected const string TEST_CONVERSATION_REFERENCE_PREFIX = 'test-audit-log-';

    protected const int TEST_INPUT_TOKENS = 100;

    protected const int TEST_OUTPUT_TOKENS = 50;

    protected const string TEST_TOOL_NAME = 'test_calculator';

    protected const string TEST_TOOL_SET_NAME = 'test_tool_set';

    protected const string TEST_TOOL_RESULT = '42';

    protected AiFoundationBusinessTester $tester;

    public function testGivenPostPromptPluginIsRegisteredWhenPromptIsCalledThenPluginIsInvokedWithRequestAndResponse(): void
    {
        // Arrange
        $postPromptPlugin = $this->createMock(PostPromptPluginInterface::class);
        $postPromptPlugin->expects($this->once())->method('postPrompt');
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT, [$postPromptPlugin]);

        // Act
        $this->createFacadeWithMockedProvider()->prompt($this->createPromptRequestTransfer());
    }

    public function testGivenNoPostPromptPluginsWhenPromptIsCalledThenFacadeReturnsValidResponse(): void
    {
        // Arrange
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT, []);

        // Act
        $promptResponseTransfer = $this->createFacadeWithMockedProvider()->prompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
    }

    public function testGivenMultiplePostPromptPluginsWhenPromptIsCalledThenAllPluginsAreInvoked(): void
    {
        // Arrange
        $firstPlugin = $this->createMock(PostPromptPluginInterface::class);
        $secondPlugin = $this->createMock(PostPromptPluginInterface::class);
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT, [$firstPlugin, $secondPlugin]);
        $firstPlugin->expects($this->once())->method('postPrompt');
        $secondPlugin->expects($this->once())->method('postPrompt');

        // Act
        $this->createFacadeWithMockedProvider()->prompt($this->createPromptRequestTransfer());
    }

    public function testGivenPlainPromptWithUsageWhenAuditLogPipelineIsWiredThenAllFieldsArePersistedInDatabase(): void
    {
        // Arrange
        $conversationReference = static::TEST_CONVERSATION_REFERENCE_PREFIX . uniqid('', true);
        $mockResponse = (new AssistantMessage(static::TEST_ASSISTANT_RESPONSE))
            ->setUsage(new Usage(inputTokens: static::TEST_INPUT_TOKENS, outputTokens: static::TEST_OUTPUT_TOKENS));

        $promptRequest = $this->createPromptRequestTransfer()->setConversationReference($conversationReference);

        // Act
        $this->createFacadeWithFullAuditPipeline($mockResponse)->prompt($promptRequest);

        // Assert
        $this->assertAiInteractionLogEntity($this->findAiInteractionLogByConversationReference($conversationReference), $conversationReference);
    }

    public function testGivenSuccessfulPromptWithoutUsageWhenAuditLogPipelineIsWiredThenIsSuccessfulIsTrueInDatabase(): void
    {
        // Arrange
        $conversationReference = static::TEST_CONVERSATION_REFERENCE_PREFIX . uniqid('', true);
        $mockResponse = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);

        // Act
        $this->createFacadeWithFullAuditPipeline($mockResponse)->prompt($this->createPromptRequestTransfer()->setConversationReference($conversationReference));

        // Assert
        $logEntity = $this->findAiInteractionLogByConversationReference($conversationReference);
        $this->assertNotNull($logEntity->getPrompt());
        $this->assertNotNull($logEntity->getResponse());
        $this->assertNotNull($logEntity->getProvider());
        $this->assertNotNull($logEntity->getModel());
        $this->assertTrue($logEntity->getIsSuccessful());
        $this->assertNull($logEntity->getInputTokens());
        $this->assertNull($logEntity->getOutputTokens());
    }

    public function testGivenShortPromptAndResponseWhenAuditLogPipelineIsWiredThenBothAreNotTruncated(): void
    {
        // Arrange
        $conversationReference = static::TEST_CONVERSATION_REFERENCE_PREFIX . uniqid('', true);
        $mockResponse = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);

        $promptRequest = $this->createPromptRequestTransfer()->setConversationReference($conversationReference);

        // Act
        $this->createFacadeWithFullAuditPipeline($mockResponse)->prompt($promptRequest);

        // Assert
        $logEntity = $this->findAiInteractionLogByConversationReference($conversationReference);

        $this->assertSame(static::TEST_USER_MESSAGE, $logEntity->getPrompt());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $logEntity->getResponse());
    }

    public function testGivenAuditLogPostToolCallPluginIsRegisteredWhenToolIsCalledThenToolCallIsPersistedToDatabase(): void
    {
        // Arrange
        $conversationReference = static::TEST_CONVERSATION_REFERENCE_PREFIX . uniqid('tool-', true);
        $testTool = $this->createTestTool();

        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT, [new AuditLogPostPromptPlugin()]);
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_AI_INTERACTION_LOG_HANDLER, [new AiInteractionHandlerPlugin()]);

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $facade = $this->createFacadeWithMockedProviderToolCallPlugins($mockProvider, [$testTool], [new AuditLogPostToolCallPlugin()]);

        $promptRequest = $this->createPromptRequestTransfer()
            ->setConversationReference($conversationReference)
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        // Act
        $facade->prompt($promptRequest);

        // Assert
        $logEntities = SpyAiInteractionLogQuery::create()
            ->filterByConversationReference($conversationReference)
            ->find();

        $toolCallLog = null;
        foreach ($logEntities as $entry) {
            $metadata = json_decode($entry->getMetadata() ?? '{}', true);
            if (($metadata['context'] ?? null) === 'POST_TOOL_CALL') {
                $toolCallLog = $entry;

                break;
            }
        }

        $this->assertNotNull($toolCallLog, 'Expected a tool call audit log entry in the database.');
        $this->assertTrue($toolCallLog->getIsSuccessful());
        $metadata = json_decode($toolCallLog->getMetadata() ?? '{}', true);
        $this->assertSame('POST_TOOL_CALL', $metadata['context'] ?? null);
        $this->assertSame(static::TEST_TOOL_NAME, $metadata['tool_name'] ?? null);
        $this->assertSame(static::TEST_TOOL_RESULT, $metadata['tool_result'] ?? null);
    }

    protected function assertAiInteractionLogEntity(SpyAiInteractionLog $logEntity, string $conversationReference): void
    {
        $this->assertSame(static::TEST_AI_ENGINE, $logEntity->getConfigurationName());
        $this->assertSame(AiFoundationConstants::PROVIDER_OLLAMA, $logEntity->getProvider());
        $this->assertSame(static::TEST_OLLAMA_MODEL, $logEntity->getModel());
        $this->assertSame(static::TEST_USER_MESSAGE, $logEntity->getPrompt());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $logEntity->getResponse());
        $this->assertSame(static::TEST_INPUT_TOKENS, $logEntity->getInputTokens());
        $this->assertSame(static::TEST_OUTPUT_TOKENS, $logEntity->getOutputTokens());
        $this->assertSame($conversationReference, $logEntity->getConversationReference());
        $this->assertNotNull($logEntity->getInferenceTimeMs());
        $this->assertTrue($logEntity->getIsSuccessful());
        $metadata = json_decode($logEntity->getMetadata() ?? '{}', true);
        $this->assertSame('POST_PROMPT', $metadata['context'] ?? null);
        $this->assertNotNull($logEntity->getCreatedAt());
    }

    protected function findAiInteractionLogByConversationReference(string $conversationReference): SpyAiInteractionLog
    {
        $entity = SpyAiInteractionLogQuery::create()
            ->filterByConversationReference($conversationReference)
            ->findOne();

        $this->assertNotNull($entity, sprintf('Expected ai_interaction_log row with conversation_reference "%s" in the database.', $conversationReference));

        return $entity;
    }

    protected function createFacadeWithFullAuditPipeline(AssistantMessage $mockResponse): AiFoundationFacadeInterface
    {
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_POST_PROMPT, [new AuditLogPostPromptPlugin()]);
        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGINS_AI_INTERACTION_LOG_HANDLER, [new AiInteractionHandlerPlugin()]);

        return $this->createFacadeWithMockedProvider($mockResponse);
    }

    protected function createTestTool(): Tool
    {
        $tool = new Tool(
            name: static::TEST_TOOL_NAME,
            description: 'A test calculator tool',
        );

        $tool->setCallable(function (): string {
            return static::TEST_TOOL_RESULT;
        });

        return $tool;
    }

    protected function createMockProviderWithToolCall(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();
        $mockProvider->method('chat')->willReturnOnConsecutiveCalls(
            new ToolCallMessage('Calling tool', [$tool]),
            new AssistantMessage(static::TEST_ASSISTANT_RESPONSE),
        );

        return $mockProvider;
    }

    /**
     * @param array<\NeuronAI\Tools\Tool> $tools
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     */
    protected function createFacadeWithMockedProviderToolCallPlugins(
        AIProviderInterface $mockProvider,
        array $tools,
        array $postToolCallPlugins,
    ): AiFoundationFacadeInterface {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->tester->getFactory()->createNeuronAiMessageMapper(),
            toolMapper: $this->tester->getFactory()->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createMockChatHistoryResolver(),
            aiConfigurations: $this->createMockConfig()->getAiConfigurations(),
            aiToolSetPlugins: $this->convertToolsToToolSets($tools),
            postPromptPlugins: $this->tester->getFactory()->getPostPromptPlugins(),
            postToolCallPlugins: $postToolCallPlugins,
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER, $mockVendorProviderPlugin);

        return $this->tester->getFacade();
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

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage((new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE));
    }

    protected function createFacadeWithMockedProvider(?AssistantMessage $mockResponse = null): AiFoundationFacadeInterface
    {
        $mockResponse ??= new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('chat')->willReturn($mockResponse);

        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->tester->getFactory()->createNeuronAiMessageMapper(),
            toolMapper: $this->tester->getFactory()->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createMockChatHistoryResolver(),
            aiConfigurations: $this->createMockConfig()->getAiConfigurations(),
            aiToolSetPlugins: [],
            postPromptPlugins: $this->tester->getFactory()->getPostPromptPlugins(),
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER, $mockVendorProviderPlugin);

        return $this->tester->getFacade();
    }

    protected function createMockConfig(): AiFoundationConfig
    {
        $config = $this->createMock(AiFoundationConfig::class);
        $config->method('getAiConfigurations')->willReturn([
            static::TEST_AI_ENGINE => [
                'provider_name' => AiFoundationConstants::PROVIDER_OLLAMA,
                'provider_config' => [
                    'url' => static::TEST_OLLAMA_URL,
                    'model' => static::TEST_OLLAMA_MODEL,
                    'parameters' => [],
                ],
                'system_prompt' => static::TEST_SYSTEM_PROMPT,
            ],
        ]);

        return $config;
    }

    protected function createMockChatHistoryResolver(): ChatHistoryResolverInterface
    {
        $mockChatHistoryResolver = $this->createMock(ChatHistoryResolverInterface::class);
        $mockChatHistoryResolver->method('resolve')->willReturn(null);

        return $mockChatHistoryResolver;
    }
}
