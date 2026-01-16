<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AiFoundation\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\Tool;
use Spryker\Client\AiFoundation\AiFoundationClient;
use Spryker\Client\AiFoundation\AiFoundationClientInterface;
use Spryker\Client\AiFoundation\AiFoundationConfig;
use Spryker\Client\AiFoundation\AiFoundationFactory;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapper;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;
use SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group NeuronAi
 * @group AiFoundationClientToolCallTest
 * Add your own group annotations below this line
 */
class AiFoundationClientToolCallTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_ollama';

    protected const string TEST_OLLAMA_URL = 'http://localhost:11434/api';

    protected const string TEST_OLLAMA_MODEL = 'llama3.2';

    protected const string TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    protected const string TEST_USER_MESSAGE = 'Execute the test tool';

    protected const string TEST_TOOL_NAME = 'test_calculator';

    protected const string TEST_TOOL_SET_NAME = 'test_tool_set';

    protected const string TEST_TOOL_RESULT = '42';

    protected AiFoundationClientTester $tester;

    public function testGivenToolIsProvidedWhenPromptWithToolCallThenToolInvocationsAreReturned(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $mockProvider = $this->createMockProviderWithToolCall($testTool);
        $client = $this->createClientWithMockedProviderAndTools($mockProvider, [$testTool]);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertNotNull($promptResponse->getMessage());
        $this->assertCount(1, $promptResponse->getToolInvocations());

        $toolInvocation = $promptResponse->getToolInvocations()->offsetGet(0);
        $this->assertSame(static::TEST_TOOL_NAME, $toolInvocation->getName());
        $this->assertSame(static::TEST_TOOL_RESULT, $toolInvocation->getResult());
    }

    public function testGivenMultipleToolCallsWhenPromptThenAllToolInvocationsAreReturned(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $mockProvider = $this->createMockProviderWithMultipleToolInvocations($testTool);
        $client = $this->createClientWithMockedProviderAndTools($mockProvider, [$testTool]);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertCount(2, $promptResponse->getToolInvocations());

        foreach ($promptResponse->getToolInvocations() as $toolInvocation) {
            $this->assertSame(static::TEST_TOOL_NAME, $toolInvocation->getName());
            $this->assertSame(static::TEST_TOOL_RESULT, $toolInvocation->getResult());
        }
    }

    public function testGivenToolWithArgumentsWhenToolIsCalledThenArgumentsAreRecorded(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $expectedArguments = ['number1' => 10, 'number2' => 32];
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $mockProvider = $this->createMockProviderWithToolCallAndArguments($testTool, $expectedArguments);
        $client = $this->createClientWithMockedProviderAndTools($mockProvider, [$testTool]);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertCount(1, $promptResponse->getToolInvocations());

        $toolInvocation = $promptResponse->getToolInvocations()->offsetGet(0);
        $this->assertSame(static::TEST_TOOL_NAME, $toolInvocation->getName());
        $this->assertSame($expectedArguments, $toolInvocation->getArguments());
        $this->assertSame(static::TEST_TOOL_RESULT, $toolInvocation->getResult());
    }

    public function testGivenStructuredResponseWithToolCallWhenPromptThenStructuredMessageAndToolInvocationsAreReturned(): void
    {
        // Arrange
        $testTool = $this->createTestTool();
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema)
            ->addToolSetName(static::TEST_TOOL_SET_NAME);

        $responseJson = json_encode([
            'rand_string' => 'Test with tool call',
            'any_object' => [
                'branch' => 'feature/tool-call',
                'message' => 'Tool call executed successfully',
            ],
            'array_of_strings' => ['tool', 'test'],
            'ai_response_paths' => [
                ['path' => '/src/test.php'],
            ],
        ]);

        $mockProvider = $this->createMockProviderWithStructuredResponseAndToolCall($testTool, $responseJson);
        $client = $this->createClientWithMockedProviderAndTools($mockProvider, [$testTool]);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());

        $result = $promptResponse->getStructuredMessage();
        $this->assertInstanceOf(AiResponseTransfer::class, $result);
        $this->assertSame('Test with tool call', $result->getRandString());

        $this->assertCount(1, $promptResponse->getToolInvocations());
        $toolInvocation = $promptResponse->getToolInvocations()->offsetGet(0);
        $this->assertSame(static::TEST_TOOL_NAME, $toolInvocation->getName());
        $this->assertSame(static::TEST_TOOL_RESULT, $toolInvocation->getResult());
    }

    public function testGivenNoToolsProvidedWhenPromptThenNoToolInvocationsAreReturned(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $mockProvider = $this->createMockProviderWithoutToolCall();
        $client = $this->createClientWithMockedProviderAndTools($mockProvider, []);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertCount(0, $promptResponse->getToolInvocations());
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

    protected function createMockProviderWithStructuredResponseAndToolCall(Tool $tool, string $responseJson): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('setTools')->willReturnSelf();

        $toolCallMessage = new ToolCallMessage('Calling tool for structured response', [$tool]);

        $finalStructuredMessage = new AssistantMessage($responseJson);

        $mockProvider->method('structured')
            ->willReturnOnConsecutiveCalls(
                $toolCallMessage,
                $finalStructuredMessage,
            );

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
     */
    protected function createClientWithMockedProviderAndTools(
        AIProviderInterface $mockProvider,
        array $tools,
    ): AiFoundationClientInterface {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->createNeuronAiMessageMapper(),
            toolMapper: $this->createNeuronAiToolMapper(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: $this->convertToolsToToolSets($tools),
        );

        $factoryMock = $this->createMock(AiFoundationFactory::class);
        $factoryMock->method('createVendorAdapter')->willReturn($neuronAiAdapter);

        $client = new AiFoundationClient();
        $client->setFactory($factoryMock);

        return $client;
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
        $transferJsonSchemaMapper = $this->createTransferJsonSchemaMapper();

        return new NeuronAiMessageMapper($transferJsonSchemaMapper);
    }

    protected function createTransferJsonSchemaMapper(): TransferJsonSchemaMapperInterface
    {
        return new TransferJsonSchemaMapper();
    }

    protected function createNeuronAiToolMapper(): NeuronAiToolMapperInterface
    {
        return new NeuronAiToolMapper();
    }

    /**
     * @param array<\NeuronAI\Tools\Tool> $tools
     *
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface>
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
}
