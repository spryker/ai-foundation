<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\Tool;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor\MessageContentExtractor;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface;
use Spryker\Zed\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface;
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
 * @group NeuronAi
 * @group Facade
 * @group AiFoundationFacadeToolCallPluginTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeToolCallPluginTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_ollama';

    protected const string TEST_OLLAMA_URL = 'http://localhost:11434/api';

    protected const string TEST_OLLAMA_MODEL = 'llama3.2';

    protected const string TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    protected const string TEST_USER_MESSAGE = 'Execute the test tool';

    protected const string TEST_TOOL_NAME = 'test_calculator';

    protected const string TEST_TOOL_SET_NAME = 'test_tool_set';

    protected const string TEST_TOOL_RESULT = '42';

    protected const string TEST_MODIFIED_BY_KEY = 'modified_by';

    protected const string TEST_FIRST_PLUGIN_VALUE = 'first_plugin';

    protected AiFoundationBusinessTester $tester;

    public function testGivenPreToolCallPluginWhenToolIsCalledThenPluginReceivesCorrectContext(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $capturedTransfer = null;
        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->expects($this->once())
            ->method('preToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $transfer) use (&$capturedTransfer) {
                $capturedTransfer = $transfer;

                return $transfer;
            });

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [$testTool],
            [$preToolCallPlugin],
            [],
        );

        // Act
        $promptResponse = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertNotNull($capturedTransfer);
        $this->assertSame(static::TEST_TOOL_NAME, $capturedTransfer->getToolName());
        $this->assertNotNull($capturedTransfer->getPromptRequest());
        $this->assertSame(static::TEST_AI_ENGINE, $capturedTransfer->getPromptRequest()->getAiConfigurationName());
        $this->assertTrue($capturedTransfer->getIsExecutionAllowed());
    }

    public function testGivenPostToolCallPluginWhenToolIsCalledThenPluginReceivesCorrectContext(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $capturedTransfer = null;
        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->expects($this->once())
            ->method('postToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $transfer) use (&$capturedTransfer): void {
                $capturedTransfer = $transfer;
            });

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [$testTool],
            [],
            [$postToolCallPlugin],
        );

        // Act
        $promptResponse = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertNotNull($capturedTransfer);
        $this->assertSame(static::TEST_TOOL_NAME, $capturedTransfer->getToolName());
        $this->assertSame(static::TEST_TOOL_RESULT, $capturedTransfer->getToolResult());
        $this->assertNotNull($capturedTransfer->getPromptRequest());
        $this->assertSame(static::TEST_AI_ENGINE, $capturedTransfer->getPromptRequest()->getAiConfigurationName());
    }

    public function testGivenPreToolCallPluginBlocksExecutionWhenToolIsCalledThenToolIsNotExecuted(): void
    {
        // Arrange
        $toolExecutionCount = 0;
        $testTool = new Tool(
            name: static::TEST_TOOL_NAME,
            description: 'A test calculator tool',
        );
        $testTool->setCallable(function () use (&$toolExecutionCount): string {
            $toolExecutionCount++;

            return static::TEST_TOOL_RESULT;
        });

        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->method('preToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $transfer): AiToolCallTransfer {
                return $transfer->setIsExecutionAllowed(false);
            });

        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->expects($this->once())->method('postToolCall');

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [$testTool],
            [$preToolCallPlugin],
            [$postToolCallPlugin],
        );

        // convertToolsToToolSets calls execute() once during setup
        $executionCountBeforePrompt = $toolExecutionCount;

        // Act
        $promptResponse = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        // Tool should NOT have been executed again during prompt (blocked by pre-plugin)
        $this->assertSame($executionCountBeforePrompt, $toolExecutionCount);
    }

    public function testGivenMultipleToolCallsWhenPromptIsCalledThenPluginsAreInvokedForEachTool(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->expects($this->exactly(2))
            ->method('preToolCall')
            ->willReturnArgument(0);

        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->expects($this->exactly(2))->method('postToolCall');

        $mockProvider = $this->createMockProviderWithMultipleToolInvocations($testTool);
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [$testTool],
            [$preToolCallPlugin],
            [$postToolCallPlugin],
        );

        // Act
        $promptResponse = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
    }

    public function testGivenNoToolCallsWhenPromptIsCalledThenPluginsAreNotInvoked(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->expects($this->never())->method('preToolCall');

        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->expects($this->never())->method('postToolCall');

        $mockProvider = $this->createMockProviderWithoutToolCall();
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [],
            [$preToolCallPlugin],
            [$postToolCallPlugin],
        );

        // Act
        $promptResponse = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
    }

    public function testGivenMultiplePreToolCallPluginsWhenToolIsCalledThenPluginsChainCorrectly(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $firstPluginCalled = false;
        $secondPluginReceivedModifiedTransfer = false;

        $firstPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $firstPlugin->method('preToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $transfer) use (&$firstPluginCalled): AiToolCallTransfer {
                $firstPluginCalled = true;
                $transfer->setToolArguments([static::TEST_MODIFIED_BY_KEY => static::TEST_FIRST_PLUGIN_VALUE]);

                return $transfer;
            });

        $secondPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $secondPlugin->method('preToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $transfer) use (&$secondPluginReceivedModifiedTransfer): AiToolCallTransfer {
                if ($transfer->getToolArguments() === [static::TEST_MODIFIED_BY_KEY => static::TEST_FIRST_PLUGIN_VALUE]) {
                    $secondPluginReceivedModifiedTransfer = true;
                }

                return $transfer;
            });

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $facade = $this->createFacadeWithMockedProviderAndTools(
            $mockProvider,
            [$testTool],
            [$firstPlugin, $secondPlugin],
            [],
        );

        // Act
        $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($firstPluginCalled);
        $this->assertTrue($secondPluginReceivedModifiedTransfer);
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

    protected function createMockProviderWithToolCall(Tool $tool): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage = new ToolCallMessage('Calling tool', [$tool]);
        $finalMessage = new AssistantMessage('Tool executed successfully');

        $mockProvider->method('chat')
            ->willReturnOnConsecutiveCalls($toolCallMessage, $finalMessage);

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
            ->willReturnOnConsecutiveCalls($toolCallMessage1, $toolCallMessage2, $finalMessage);

        return $mockProvider;
    }

    protected function createMockProviderWithoutToolCall(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $mockProvider->method('chat')
            ->willReturn(new AssistantMessage('Simple response without tool calls'));

        return $mockProvider;
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @param array<\NeuronAI\Tools\Tool> $tools
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface> $preToolCallPlugins
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     */
    protected function createFacadeWithMockedProviderAndTools(
        AIProviderInterface $mockProvider,
        array $tools,
        array $preToolCallPlugins = [],
        array $postToolCallPlugins = [],
    ): AiFoundationFacadeInterface {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->createNeuronAiMessageMapper(),
            toolMapper: $this->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createMockChatHistoryResolver(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: $this->convertToolsToToolSets($tools),
            postPromptPlugins: [],
            preToolCallPlugins: $preToolCallPlugins,
            postToolCallPlugins: $postToolCallPlugins,
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $mockVendorProviderPlugin,
        );

        return $this->tester->getFacade();
    }

    protected function createMockConfig(): AiFoundationConfig
    {
        $config = $this->createMock(AiFoundationConfig::class);

        $config->method('getAiConfigurations')
            ->willReturn([
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

    protected function createNeuronAiMessageMapper(): NeuronAiMessageMapper
    {
        return new NeuronAiMessageMapper(new TransferJsonSchemaMapper(), new MessageContentExtractor());
    }

    protected function createNeuronAiToolMapper(): NeuronAiToolMapperInterface
    {
        return new NeuronAiToolMapper();
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

    protected function createMockChatHistoryResolver(): ChatHistoryResolverInterface
    {
        $mockChatHistoryResolver = $this->createMock(ChatHistoryResolverInterface::class);
        $mockChatHistoryResolver->method('resolve')->willReturn(null);

        return $mockChatHistoryResolver;
    }
}
