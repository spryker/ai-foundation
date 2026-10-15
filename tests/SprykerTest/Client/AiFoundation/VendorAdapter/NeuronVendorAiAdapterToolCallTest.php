<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiConfigurationTransfer;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Tools\Tool;
use RuntimeException;
use Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface;
use Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactory;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\ClientNeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\StreamChunkMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ClientProviderResolverInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group VendorAdapter
 * @group NeuronVendorAiAdapterToolCallTest
 * Add your own group annotations below this line
 */
class NeuronVendorAiAdapterToolCallTest extends Unit
{
    protected const string AI_CONFIGURATION_NAME = 'test_configuration';

    protected const string PROVIDER_NAME = 'test_provider';

    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const string USER_MESSAGE = 'Add two numbers';

    protected const string TOOL_NAME = 'test_calculator';

    protected const string PROVIDER_TOOL_CALL_ID = 'call_provider_generated_1';

    protected const string PROVIDER_TOOL_CALL_ID_SECOND = 'call_provider_generated_2';

    protected const string TOOL_RESULT = '42';

    protected const string FINAL_ANSWER = 'The result is 42';

    protected const string TOOL_SKIPPED_RESULT = 'The tool was not executed because it was not permitted.';

    protected const string TOOL_FAILURE_RESULT = 'The tool could not complete and returned no result.';

    protected const string ERROR_MESSAGE_TOOL_BROKEN = 'Tool is broken.';

    protected const int MAX_TOOL_LOOP_ITERATIONS = 5;

    protected const string UNREGISTERED_TOOL_NAME = 'unregistered_tool';

    protected const int TOOL_CALL_INPUT_TOKENS = 100;

    protected const int TOOL_CALL_OUTPUT_TOKENS = 10;

    protected const int FINAL_INPUT_TOKENS = 150;

    protected const int FINAL_OUTPUT_TOKENS = 20;

    protected AiFoundationClientTester $tester;

    public function testGivenToolCallWhenStreamingPromptThenToolIsExecutedAndTurnAnswers(): void
    {
        // Arrange
        $tool = $this->createTool();

        // Act
        $promptResponseTransfer = $this->createAdapter([[$tool]])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(static::TOOL_RESULT, $tool->getResult());
    }

    public function testGivenToolCallWhenStreamingPromptThenPreToolCallPluginReceivesToolNameAndPromptRequest(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $capturedToolCallTransfer = null;

        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->expects($this->once())
            ->method('preToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $aiToolCallTransfer) use (&$capturedToolCallTransfer): AiToolCallTransfer {
                $capturedToolCallTransfer = $aiToolCallTransfer;

                return $aiToolCallTransfer;
            });

        // Act
        $this->createAdapter([[$this->createTool()]], [$preToolCallPlugin])
            ->streamPrompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertSame(static::TOOL_NAME, $capturedToolCallTransfer->getToolName());
        $this->assertSame($promptRequestTransfer, $capturedToolCallTransfer->getPromptRequest());
        $this->assertTrue($capturedToolCallTransfer->getIsExecutionAllowed());
    }

    public function testGivenDenyingPreToolCallPluginWhenStreamingPromptThenToolIsNotExecuted(): void
    {
        // Arrange
        $tool = $this->createTool();

        // Act
        $this->createAdapter([[$tool]], [$this->createDenyingPreToolCallPlugin()])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertSame(static::TOOL_SKIPPED_RESULT, $tool->getResult());
    }

    public function testGivenToolCallWhenStreamingPromptThenPostToolCallPluginReceivesToolResult(): void
    {
        // Arrange
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);

        // Act
        $this->createAdapter([[$this->createTool()]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertSame(static::TOOL_NAME, $capturedToolCallTransfer->getToolName());
        $this->assertSame(static::TOOL_RESULT, $capturedToolCallTransfer->getToolResult());
    }

    public function testGivenDeniedToolCallWhenStreamingPromptThenPostToolCallPluginRuns(): void
    {
        // Arrange
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);

        // Act
        $this->createAdapter(
            [[$this->createTool()]],
            [$this->createDenyingPreToolCallPlugin()],
            [$postToolCallPlugin],
        )->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertFalse($capturedToolCallTransfer->getIsExecutionAllowed());
        $this->assertSame(static::TOOL_SKIPPED_RESULT, $capturedToolCallTransfer->getToolResult());
    }

    public function testGivenFailingToolWhenStreamingPromptThenTurnSucceedsWithFailureResult(): void
    {
        // Arrange
        $tool = $this->createFailingTool();
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);

        // Act
        $promptResponseTransfer = $this->createAdapter([[$tool]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertSame(static::TOOL_FAILURE_RESULT, $capturedToolCallTransfer->getToolResult());
    }

    public function testGivenSeveralToolCallsInOneRoundWhenStreamingPromptThenEveryToolIsExecuted(): void
    {
        // Arrange
        $firstTool = $this->createTool();
        $secondTool = $this->createTool();
        $capturedToolNames = [];

        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->method('postToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $aiToolCallTransfer) use (&$capturedToolNames): void {
                $capturedToolNames[] = (string)$aiToolCallTransfer->getToolName();
            });

        // Act
        $this->createAdapter([[$firstTool, $secondTool]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertSame([static::TOOL_NAME, static::TOOL_NAME], $capturedToolNames);
        $this->assertSame(static::TOOL_RESULT, $secondTool->getResult());
    }

    public function testGivenEndlessToolCallsWhenStreamingPromptThenLoopIsCappedAndExhaustionIsReported(): void
    {
        // Arrange
        $toolCallRounds = array_fill(0, static::MAX_TOOL_LOOP_ITERATIONS + 1, [$this->createTool()]);

        // Act
        $promptResponseTransfer = $this->createAdapter($toolCallRounds)
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertFalse($promptResponseTransfer->getIsSuccessful());
        $this->assertStringContainsString(
            (string)static::MAX_TOOL_LOOP_ITERATIONS,
            (string)$promptResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
    }

    public function testGivenSeveralPreToolCallPluginsWhenStreamingPromptThenTheyAreChainedInOrder(): void
    {
        // Arrange
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);

        $allowingPreToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $allowingPreToolCallPlugin->method('preToolCall')
            ->willReturnCallback(static fn (AiToolCallTransfer $aiToolCallTransfer): AiToolCallTransfer => $aiToolCallTransfer->setIsExecutionAllowed(true));

        // Act
        $this->createAdapter(
            [[$this->createTool()]],
            [$allowingPreToolCallPlugin, $this->createDenyingPreToolCallPlugin()],
            [$postToolCallPlugin],
        )->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertFalse($capturedToolCallTransfer->getIsExecutionAllowed());
        $this->assertSame(static::TOOL_SKIPPED_RESULT, $capturedToolCallTransfer->getToolResult());
    }

    public function testGivenAnswerWithoutToolCallWhenStreamingPromptThenNoToolCallPluginRuns(): void
    {
        // Arrange
        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);

        // Assert
        $preToolCallPlugin->expects($this->never())->method('preToolCall');
        $postToolCallPlugin->expects($this->never())->method('postToolCall');

        // Act
        $promptResponseTransfer = $this->createAdapter([], [$preToolCallPlugin], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
    }

    public function testGivenProviderToolCallIdWhenStreamingPromptThenToolCallPluginsReceiveIt(): void
    {
        // Arrange
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);
        $tool = $this->createTool()->setCallId(static::PROVIDER_TOOL_CALL_ID);

        // Act
        $this->createAdapter([[$tool]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertSame(static::PROVIDER_TOOL_CALL_ID, $capturedToolCallTransfer->getToolCallId());
    }

    public function testGivenNoProviderToolCallIdWhenStreamingPromptThenToolCallIdIsNull(): void
    {
        // Arrange
        $capturedToolCallTransfer = null;
        $postToolCallPlugin = $this->createRecordingPostToolCallPlugin($capturedToolCallTransfer);

        // Act
        $this->createAdapter([[$this->createTool()]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertNotNull($capturedToolCallTransfer);
        $this->assertNull($capturedToolCallTransfer->getToolCallId());
    }

    public function testGivenParallelCallsToSameToolWhenStreamingPromptThenEachKeepsItsOwnToolCallId(): void
    {
        // Arrange
        $firstTool = $this->createTool()->setCallId(static::PROVIDER_TOOL_CALL_ID);
        $secondTool = $this->createTool()->setCallId(static::PROVIDER_TOOL_CALL_ID_SECOND);
        $capturedToolCallIds = [];

        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->method('postToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $aiToolCallTransfer) use (&$capturedToolCallIds): void {
                $capturedToolCallIds[] = $aiToolCallTransfer->getToolCallId();
            });

        // Act
        $this->createAdapter([[$firstTool, $secondTool]], [], [$postToolCallPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertSame(
            [static::PROVIDER_TOOL_CALL_ID, static::PROVIDER_TOOL_CALL_ID_SECOND],
            $capturedToolCallIds,
        );
    }

    public function testGivenUnregisteredToolNameWhenStreamingPromptThenTurnFailsNamingTheTool(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->addToolName(static::TOOL_NAME)
            ->addToolName(static::UNREGISTERED_TOOL_NAME);

        // Act
        $promptResponseTransfer = $this->createAdapter([[$this->createTool()]], [], [], [$this->createToolPlugin(static::TOOL_NAME)])
            ->streamPrompt($promptRequestTransfer);

        // Assert
        $this->assertFalse($promptResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $promptResponseTransfer->getErrors());
        $this->assertStringContainsString(
            static::UNREGISTERED_TOOL_NAME,
            (string)$promptResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
        $this->assertStringNotContainsString(
            static::TOOL_NAME,
            (string)$promptResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
    }

    public function testGivenRegisteredToolNamesWhenStreamingPromptThenTurnSucceeds(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer()->addToolName(static::TOOL_NAME);

        // Act
        $promptResponseTransfer = $this->createAdapter([[$this->createTool()]], [], [], [$this->createToolPlugin(static::TOOL_NAME)])
            ->streamPrompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
    }

    public function testGivenToolCallWithUsageWhenStreamingPromptThenResponseUsageIsSumOfAllRoundTrips(): void
    {
        // Arrange
        $adapter = $this->createAdapterWithUsage([[$this->createTool()]]);

        // Act
        $promptResponseTransfer = $adapter->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(static::TOOL_CALL_INPUT_TOKENS + static::FINAL_INPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getInputTokens());
        $this->assertSame(static::TOOL_CALL_OUTPUT_TOKENS + static::FINAL_OUTPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getOutputTokens());
    }

    public function testGivenEndlessToolCallsWithUsageWhenStreamingPromptThenResponseUsageIsSumOfAllRoundTrips(): void
    {
        // Arrange
        $roundTripCount = static::MAX_TOOL_LOOP_ITERATIONS + 1;
        $adapter = $this->createAdapterWithUsage(array_fill(0, $roundTripCount, [$this->createTool()]));

        // Act
        $promptResponseTransfer = $adapter->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertFalse($promptResponseTransfer->getIsSuccessful());
        $this->assertSame($roundTripCount * static::TOOL_CALL_INPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getInputTokens());
        $this->assertSame($roundTripCount * static::TOOL_CALL_OUTPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getOutputTokens());
    }

    public function testGivenStreamFailureAfterToolCallWithUsageWhenStreamingPromptThenResponseUsageIsSumOfCompletedRoundTrips(): void
    {
        // Arrange
        $adapter = $this->createAdapterWithUsage([[$this->createTool()]], true);

        // Act
        $promptResponseTransfer = $adapter->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertFalse($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(static::TOOL_CALL_INPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getInputTokens());
        $this->assertSame(static::TOOL_CALL_OUTPUT_TOKENS, $promptResponseTransfer->getMessageOrFail()->getUsageOrFail()->getOutputTokens());
    }

    protected function createToolPlugin(string $toolName): ToolPluginInterface
    {
        $toolPluginMock = $this->createMock(ToolPluginInterface::class);
        $toolPluginMock->method('getName')->willReturn($toolName);

        return $toolPluginMock;
    }

    protected function createDenyingPreToolCallPlugin(): PreToolCallPluginInterface
    {
        $preToolCallPlugin = $this->createMock(PreToolCallPluginInterface::class);
        $preToolCallPlugin->method('preToolCall')
            ->willReturnCallback(static fn (AiToolCallTransfer $aiToolCallTransfer): AiToolCallTransfer => $aiToolCallTransfer->setIsExecutionAllowed(false));

        return $preToolCallPlugin;
    }

    protected function createRecordingPostToolCallPlugin(?AiToolCallTransfer &$capturedToolCallTransfer): PostToolCallPluginInterface
    {
        $postToolCallPlugin = $this->createMock(PostToolCallPluginInterface::class);
        $postToolCallPlugin->method('postToolCall')
            ->willReturnCallback(function (AiToolCallTransfer $aiToolCallTransfer) use (&$capturedToolCallTransfer): void {
                $capturedToolCallTransfer = $aiToolCallTransfer;
            });

        return $postToolCallPlugin;
    }

    protected function createTool(): Tool
    {
        $tool = new Tool(static::TOOL_NAME, 'A test calculator tool.');
        $tool->setCallable(static fn (): string => static::TOOL_RESULT);

        return $tool;
    }

    protected function createFailingTool(): Tool
    {
        $tool = new Tool(static::TOOL_NAME, 'A tool that always fails.');
        $tool->setCallable(static function (): string {
            throw new RuntimeException(static::ERROR_MESSAGE_TOOL_BROKEN);
        });

        return $tool;
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::AI_CONFIGURATION_NAME)
            ->setConversationReference(static::CONVERSATION_REFERENCE)
            ->setPromptMessage((new PromptMessageTransfer())->setContent(static::USER_MESSAGE));
    }

    /**
     * @param array<int, array<int, \NeuronAI\Tools\ToolInterface>> $toolCallRounds
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface> $preToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface> $aiToolPlugins
     */
    protected function createAdapter(
        array $toolCallRounds = [],
        array $preToolCallPlugins = [],
        array $postToolCallPlugins = [],
        array $aiToolPlugins = []
    ): NeuronVendorAiAdapter {
        return $this->createAdapterForProvider(
            new ScriptedToolCallAiProvider($toolCallRounds, static::FINAL_ANSWER),
            $preToolCallPlugins,
            $postToolCallPlugins,
            $aiToolPlugins,
        );
    }

    /**
     * @param array<int, array<int, \NeuronAI\Tools\ToolInterface>> $toolCallRounds
     */
    protected function createAdapterWithUsage(array $toolCallRounds, bool $isFinalRoundFailing = false): NeuronVendorAiAdapter
    {
        return $this->createAdapterForProvider(new ScriptedToolCallAiProvider(
            $toolCallRounds,
            static::FINAL_ANSWER,
            new Usage(static::TOOL_CALL_INPUT_TOKENS, static::TOOL_CALL_OUTPUT_TOKENS),
            new Usage(static::FINAL_INPUT_TOKENS, static::FINAL_OUTPUT_TOKENS),
            $isFinalRoundFailing,
        ));
    }

    /**
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface> $preToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface> $aiToolPlugins
     */
    protected function createAdapterForProvider(
        ScriptedToolCallAiProvider $scriptedToolCallAiProvider,
        array $preToolCallPlugins = [],
        array $postToolCallPlugins = [],
        array $aiToolPlugins = []
    ): NeuronVendorAiAdapter {
        $aiFoundationStubMock = $this->createMock(AiFoundationStubInterface::class);
        $aiFoundationStubMock->method('initChatSession')->willReturn($this->createChatSessionResponseTransfer());

        $clientProviderResolverMock = $this->createMock(ClientProviderResolverInterface::class);
        $clientProviderResolverMock->method('resolve')->willReturn($scriptedToolCallAiProvider);

        return new NeuronVendorAiAdapter(
            $aiFoundationStubMock,
            new GatewayChatHistoryFactory($aiFoundationStubMock),
            $clientProviderResolverMock,
            new NeuronAiMessageMapper(),
            $this->createMock(ClientNeuronAiToolMapperInterface::class),
            new StreamChunkMapper(),
            $aiToolPlugins,
            [],
            $preToolCallPlugins,
            $postToolCallPlugins,
            [],
        );
    }

    protected function createChatSessionResponseTransfer(): ChatSessionResponseTransfer
    {
        return (new ChatSessionResponseTransfer())
            ->setIsSuccessful(true)
            ->setAiConfiguration(
                (new AiConfigurationTransfer())
                    ->setName(static::AI_CONFIGURATION_NAME)
                    ->setProviderName(static::PROVIDER_NAME)
                    ->setProviderConfig([]),
            );
    }
}
