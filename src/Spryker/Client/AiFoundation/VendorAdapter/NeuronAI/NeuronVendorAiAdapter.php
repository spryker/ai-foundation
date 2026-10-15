<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI;

use ArrayObject;
use Generated\Shared\Transfer\AiConfigurationTransfer;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Generated\Shared\Transfer\PromptStreamChunkTransfer;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolResultMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface;
use Spryker\Client\AiFoundation\Exception\ToolNotRegisteredException;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistory;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactoryInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\ClientNeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\StreamChunkMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ClientProviderResolverInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\AiFoundation\Stream\PromptStreamChunkType;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class NeuronVendorAiAdapter implements VendorAdapterInterface
{
    use LoggerTrait;

    protected const string PROVIDER_CONFIG_KEY_MODEL = 'model';

    protected const int MAX_TOOL_LOOP_ITERATIONS = 5;

    protected const string TOOL_FAILURE_RESULT = 'The tool could not complete and returned no result.';

    protected const string TOOL_SKIPPED_RESULT = 'The tool was not executed because it was not permitted.';

    protected const string ERROR_MESSAGE_TOOL_LOOP_EXHAUSTED = 'The assistant did not produce an answer: the tool call loop was capped after %d iterations.';

    protected const string ERROR_MESSAGE_TOOL_NOT_REGISTERED = 'The requested tools "%s" are not registered as Client tool plugins.';

    protected const string LOG_MESSAGE_POST_PROMPT_PLUGIN_FAILED = 'Post prompt plugin failed: %s';

    protected const string LOG_MESSAGE_STREAM_EVENT_PLUGIN_FAILED = 'Stream event plugin failed: %s';

    protected const string LOG_MESSAGE_TOOL_FAILED = 'Tool "%s" failed: %s';

    protected const string LOG_MESSAGE_CHAT_HISTORY_NOT_PERSISTED = 'Chat history of conversation "%s" could not be persisted: %s';

    protected const string LOG_MESSAGE_TOOL_RESULT_NOT_APPLIED = 'The result of tool "%s" could not be applied because %s does not expose a result setter: the model may receive a stale result.';

    protected const string MESSAGE_METADATA_KEY_TRUNCATED = 'is_truncated';

    protected const string MESSAGE_METADATA_VALUE_TRUNCATED = 'true';

    protected string $accumulatedContent = '';

    /**
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface> $aiToolPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface> $postPromptPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface> $preToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface> $streamEventPlugins
     */
    public function __construct(
        protected AiFoundationStubInterface $aiFoundationStub,
        protected GatewayChatHistoryFactoryInterface $gatewayChatHistoryFactory,
        protected ClientProviderResolverInterface $clientProviderResolver,
        protected NeuronAiMessageMapperInterface $neuronAiMessageMapper,
        protected ClientNeuronAiToolMapperInterface $clientNeuronAiToolMapper,
        protected StreamChunkMapperInterface $streamChunkMapper,
        protected array $aiToolPlugins = [],
        protected array $postPromptPlugins = [],
        protected array $preToolCallPlugins = [],
        protected array $postToolCallPlugins = [],
        protected array $streamEventPlugins = []
    ) {
    }

    public function streamPrompt(
        PromptRequestTransfer $promptRequestTransfer
    ): PromptResponseTransfer {
        $chatSessionRequestTransfer = $this->createChatSessionRequestTransfer($promptRequestTransfer);
        $chatSessionResponseTransfer = $this->aiFoundationStub->initChatSession($chatSessionRequestTransfer);

        if ($chatSessionResponseTransfer->getIsSuccessful() !== true || $chatSessionResponseTransfer->getAiConfiguration() === null) {
            return $this->rejectSession($chatSessionResponseTransfer);
        }

        $aiConfigurationTransfer = $this->applySystemPromptOverride(
            $chatSessionResponseTransfer->getAiConfigurationOrFail(),
            $promptRequestTransfer,
        );

        $gatewayChatHistory = $this->gatewayChatHistoryFactory->createGatewayChatHistory(
            $chatSessionRequestTransfer,
            $chatSessionResponseTransfer,
        );

        $this->accumulatedContent = '';

        $promptResponseTransfer = $this->streamTurn(
            $promptRequestTransfer,
            $aiConfigurationTransfer,
            $gatewayChatHistory,
        );

        $promptResponseTransfer->setStreamedContent($this->accumulatedContent);

        $this->persistTurn($promptRequestTransfer, $promptResponseTransfer, $gatewayChatHistory);

        return $this->executePostPromptPlugins($promptRequestTransfer, $promptResponseTransfer);
    }

    protected function streamTurn(
        PromptRequestTransfer $promptRequestTransfer,
        AiConfigurationTransfer $aiConfigurationTransfer,
        ChatHistoryInterface $chatHistory
    ): PromptResponseTransfer {
        $chatHistory->addMessage(
            $this->neuronAiMessageMapper->mapPromptMessageToUserMessage($promptRequestTransfer->getPromptMessageOrFail()),
        );

        $startedAt = microtime(true);
        $usages = new ArrayObject();

        try {
            $message = $this->executeStream(
                $promptRequestTransfer,
                $aiConfigurationTransfer,
                $chatHistory,
                $usages,
            );

            if ($message instanceof ToolCallMessage) {
                return $this->expandPromptResponseWithUsage(
                    $this->handleToolLoopExhaustion($aiConfigurationTransfer, $startedAt),
                    $usages,
                );
            }

            $chatHistory->addMessage($message);

            return $this->expandPromptResponseWithUsage(
                $this->createSuccessfulPromptResponse($message, $aiConfigurationTransfer, $startedAt),
                $usages,
            );
        } catch (Throwable $throwable) {
            return $this->expandPromptResponseWithUsage(
                $this->handleStreamFailure($throwable, $aiConfigurationTransfer, $startedAt),
                $usages,
            );
        }
    }

    /**
     * @param \ArrayObject<int, \NeuronAI\Chat\Messages\Usage|null> $usages
     */
    protected function executeStream(
        PromptRequestTransfer $promptRequestTransfer,
        AiConfigurationTransfer $aiConfigurationTransfer,
        ChatHistoryInterface $chatHistory,
        ArrayObject $usages
    ): Message {
        $provider = $this->clientProviderResolver->resolve(
            (string)$aiConfigurationTransfer->getProviderName(),
            $aiConfigurationTransfer->getProviderConfig(),
        );

        $systemPrompt = $aiConfigurationTransfer->getSystemPrompt();

        if ($systemPrompt !== null && $systemPrompt !== '') {
            $provider->systemPrompt($systemPrompt);
        }

        $this->setToolsToProvider($provider, $promptRequestTransfer);

        return $this->runToolLoop($provider, $chatHistory, $promptRequestTransfer, $usages);
    }

    /**
     * @param \ArrayObject<int, \NeuronAI\Chat\Messages\Usage|null> $usages
     */
    protected function runToolLoop(
        AIProviderInterface $provider,
        ChatHistoryInterface $chatHistory,
        PromptRequestTransfer $promptRequestTransfer,
        ArrayObject $usages
    ): Message {
        $message = $this->consumeStream($provider, $chatHistory, $promptRequestTransfer);
        $usages->append($message->getUsage());

        $iteration = 0;

        while ($message instanceof ToolCallMessage && $iteration < static::MAX_TOOL_LOOP_ITERATIONS) {
            $iteration++;

            $executedTools = $this->executeToolCalls($message, $promptRequestTransfer);

            $chatHistory->addMessage($message);
            $chatHistory->addMessage(new ToolResultMessage($executedTools));

            $message = $this->consumeStream($provider, $chatHistory, $promptRequestTransfer);
            $usages->append($message->getUsage());
        }

        return $message;
    }

    protected function consumeStream(
        AIProviderInterface $provider,
        ChatHistoryInterface $chatHistory,
        PromptRequestTransfer $promptRequestTransfer
    ): Message {
        $generator = $provider->stream(...$chatHistory->getMessages());

        foreach ($generator as $streamChunk) {
            $promptStreamChunkTransfer = $this->streamChunkMapper->mapStreamChunkToPromptStreamChunk($streamChunk);

            if ($promptStreamChunkTransfer !== null) {
                $this->executeStreamEventPlugins($promptStreamChunkTransfer, $promptRequestTransfer);
            }
        }

        return $generator->getReturn();
    }

    protected function executeStreamEventPlugins(
        PromptStreamChunkTransfer $promptStreamChunkTransfer,
        PromptRequestTransfer $promptRequestTransfer
    ): void {
        if ($promptStreamChunkTransfer->getType() === PromptStreamChunkType::Text->value) {
            $this->accumulatedContent .= (string)$promptStreamChunkTransfer->getContent();
        }

        foreach ($this->streamEventPlugins as $streamEventPlugin) {
            try {
                $streamEventPlugin->onStreamEvent($promptStreamChunkTransfer, $promptRequestTransfer);
            } catch (Throwable $throwable) {
                $this->getLogger()->error(
                    sprintf(static::LOG_MESSAGE_STREAM_EVENT_PLUGIN_FAILED, $throwable->getMessage()),
                    ['exception' => $throwable],
                );
            }
        }
    }

    /**
     * @return array<\NeuronAI\Tools\ToolInterface>
     */
    protected function executeToolCalls(
        ToolCallMessage $toolCallMessage,
        PromptRequestTransfer $promptRequestTransfer
    ): array {
        $tools = $toolCallMessage->getTools();

        foreach ($tools as $tool) {
            $this->executeToolCall($tool, $promptRequestTransfer);
        }

        return $tools;
    }

    protected function executeToolCall(
        ToolInterface $tool,
        PromptRequestTransfer $promptRequestTransfer
    ): void {
        $aiToolCallTransfer = $this->executePreToolCallPlugins(
            $this->createAiToolCallTransfer($tool, $promptRequestTransfer),
        );

        if ($aiToolCallTransfer->getIsExecutionAllowed() === false) {
            $this->setToolResult($tool, static::TOOL_SKIPPED_RESULT);
            $this->executePostToolCallPlugins($aiToolCallTransfer->setToolResult($tool->getResult()));

            return;
        }

        $this->executeTool($tool);

        $this->executePostToolCallPlugins($aiToolCallTransfer->setToolResult($tool->getResult()));
    }

    protected function createAiToolCallTransfer(
        ToolInterface $tool,
        PromptRequestTransfer $promptRequestTransfer
    ): AiToolCallTransfer {
        return (new AiToolCallTransfer())
            ->setToolCallId($tool->getCallId())
            ->setToolName($tool->getName())
            ->setToolArguments($tool->getInputs())
            ->setPromptRequest($promptRequestTransfer)
            ->setIsExecutionAllowed(true);
    }

    protected function executePreToolCallPlugins(AiToolCallTransfer $aiToolCallTransfer): AiToolCallTransfer
    {
        foreach ($this->preToolCallPlugins as $preToolCallPlugin) {
            $aiToolCallTransfer = $preToolCallPlugin->preToolCall($aiToolCallTransfer);
        }

        return $aiToolCallTransfer;
    }

    protected function executePostToolCallPlugins(AiToolCallTransfer $aiToolCallTransfer): void
    {
        foreach ($this->postToolCallPlugins as $postToolCallPlugin) {
            $postToolCallPlugin->postToolCall($aiToolCallTransfer);
        }
    }

    protected function executePostPromptPlugins(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer
    ): PromptResponseTransfer {
        foreach ($this->postPromptPlugins as $postPromptPlugin) {
            try {
                $postPromptPlugin->postPrompt($promptRequestTransfer, $promptResponseTransfer);
            } catch (Throwable $throwable) {
                $this->getLogger()->error(
                    sprintf(static::LOG_MESSAGE_POST_PROMPT_PLUGIN_FAILED, $throwable->getMessage()),
                    ['exception' => $throwable],
                );
            }
        }

        return $promptResponseTransfer;
    }

    protected function executeTool(ToolInterface $tool): bool
    {
        try {
            $tool->execute();

            return true;
        } catch (Throwable $throwable) {
            $this->getLogger()->error(
                sprintf(static::LOG_MESSAGE_TOOL_FAILED, $tool->getName(), $throwable->getMessage()),
                ['exception' => $throwable],
            );

            $this->setToolResult($tool, static::TOOL_FAILURE_RESULT);

            return false;
        }
    }

    protected function setToolResult(ToolInterface $tool, string $result): bool
    {
        if (!$tool instanceof Tool) {
            $this->getLogger()->warning(
                sprintf(static::LOG_MESSAGE_TOOL_RESULT_NOT_APPLIED, $tool->getName(), $tool::class),
            );

            return false;
        }

        $tool->setResult($result);

        return true;
    }

    /**
     * @throws \Spryker\Client\AiFoundation\Exception\ToolNotRegisteredException
     */
    protected function setToolsToProvider(AIProviderInterface $provider, PromptRequestTransfer $promptRequestTransfer): void
    {
        $toolNames = $promptRequestTransfer->getToolNames();

        if ($toolNames === []) {
            return;
        }

        $tools = array_values(array_filter(
            $this->aiToolPlugins,
            static fn (ToolPluginInterface $tool): bool => in_array($tool->getName(), $toolNames, true),
        ));

        $unregisteredToolNames = array_diff(
            $toolNames,
            array_map(static fn (ToolPluginInterface $tool): string => $tool->getName(), $tools),
        );

        if ($unregisteredToolNames !== []) {
            throw new ToolNotRegisteredException(
                sprintf(static::ERROR_MESSAGE_TOOL_NOT_REGISTERED, implode('", "', $unregisteredToolNames)),
            );
        }

        $provider->setTools($this->clientNeuronAiToolMapper->mapToolsToNeuronTools($tools));
    }

    protected function persistTurn(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
        GatewayChatHistory $gatewayChatHistory
    ): void {
        $conversationReference = (string)$promptRequestTransfer->getConversationReference();

        if ($conversationReference === '') {
            return;
        }

        try {
            $this->flushTurn($promptResponseTransfer, $gatewayChatHistory, $conversationReference);
        } catch (Throwable $throwable) {
            $this->getLogger()->error(
                sprintf(static::LOG_MESSAGE_CHAT_HISTORY_NOT_PERSISTED, $conversationReference, $throwable->getMessage()),
                ['exception' => $throwable],
            );
        }
    }

    protected function flushTurn(
        PromptResponseTransfer $promptResponseTransfer,
        GatewayChatHistory $gatewayChatHistory,
        string $conversationReference
    ): void {
        $isTruncated = $promptResponseTransfer->getIsSuccessful() !== true;
        $assistantContent = $this->resolveAssistantContent($promptResponseTransfer);

        if ($isTruncated && $assistantContent === '') {
            return;
        }

        if ($isTruncated) {
            $gatewayChatHistory->addMessage(
                (new AssistantMessage($assistantContent))
                    ->addMetadata(static::MESSAGE_METADATA_KEY_TRUNCATED, static::MESSAGE_METADATA_VALUE_TRUNCATED),
            );
        }

        $conversationHistoryCollectionResponseTransfer = $gatewayChatHistory->flush();

        if ($conversationHistoryCollectionResponseTransfer->getIsSuccessful() === true) {
            return;
        }

        $errorMessages = [];

        foreach ($conversationHistoryCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $errorMessages[] = (string)$errorTransfer->getMessage();
        }

        $this->getLogger()->error(
            sprintf(static::LOG_MESSAGE_CHAT_HISTORY_NOT_PERSISTED, $conversationReference, implode('; ', $errorMessages)),
        );
    }

    protected function resolveAssistantContent(PromptResponseTransfer $promptResponseTransfer): string
    {
        $messageContent = (string)$promptResponseTransfer->getMessage()?->getContent();

        if ($messageContent !== '') {
            return $messageContent;
        }

        return (string)$promptResponseTransfer->getStreamedContent();
    }

    protected function applySystemPromptOverride(
        AiConfigurationTransfer $aiConfigurationTransfer,
        PromptRequestTransfer $promptRequestTransfer
    ): AiConfigurationTransfer {
        $systemPrompt = trim((string)$promptRequestTransfer->getSystemPrompt());

        if ($systemPrompt === '') {
            return $aiConfigurationTransfer;
        }

        return $aiConfigurationTransfer->setSystemPrompt($systemPrompt);
    }

    protected function createChatSessionRequestTransfer(
        PromptRequestTransfer $promptRequestTransfer
    ): ChatSessionRequestTransfer {
        return (new ChatSessionRequestTransfer())
            ->setAiConfigurationName($promptRequestTransfer->getAiConfigurationName())
            ->setConversationReference($promptRequestTransfer->getConversationReference());
    }

    protected function rejectSession(
        ChatSessionResponseTransfer $chatSessionResponseTransfer
    ): PromptResponseTransfer {
        return (new PromptResponseTransfer())
            ->setIsSuccessful(false)
            ->setErrors($chatSessionResponseTransfer->getErrors());
    }

    protected function createSuccessfulPromptResponse(
        Message $message,
        AiConfigurationTransfer $aiConfigurationTransfer,
        float $startedAt
    ): PromptResponseTransfer {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent($message->getContent())
            ->setUsage($this->neuronAiMessageMapper->mapUsageToUsageTransfer($message->getUsage()));

        return $this->expandPromptResponseWithProviderContext(
            (new PromptResponseTransfer())
                ->setMessage($promptMessageTransfer)
                ->setIsSuccessful(true),
            $aiConfigurationTransfer,
            $startedAt,
        );
    }

    protected function handleToolLoopExhaustion(
        AiConfigurationTransfer $aiConfigurationTransfer,
        float $startedAt
    ): PromptResponseTransfer {
        $errorTransfer = (new ErrorTransfer())->setMessage(
            sprintf(static::ERROR_MESSAGE_TOOL_LOOP_EXHAUSTED, static::MAX_TOOL_LOOP_ITERATIONS),
        );

        return $this->expandPromptResponseWithProviderContext(
            (new PromptResponseTransfer())
                ->setIsSuccessful(false)
                ->addError($errorTransfer),
            $aiConfigurationTransfer,
            $startedAt,
        );
    }

    protected function handleStreamFailure(
        Throwable $throwable,
        AiConfigurationTransfer $aiConfigurationTransfer,
        float $startedAt
    ): PromptResponseTransfer {
        $errorTransfer = (new ErrorTransfer())->setMessage($throwable->getMessage());

        return $this->expandPromptResponseWithProviderContext(
            (new PromptResponseTransfer())
                ->setIsSuccessful(false)
                ->addError($errorTransfer),
            $aiConfigurationTransfer,
            $startedAt,
        );
    }

    /**
     * @param \ArrayObject<int, \NeuronAI\Chat\Messages\Usage|null> $usages
     */
    protected function expandPromptResponseWithUsage(
        PromptResponseTransfer $promptResponseTransfer,
        ArrayObject $usages
    ): PromptResponseTransfer {
        $usageTransfer = $this->neuronAiMessageMapper->mapUsagesToUsageTransfer($usages->getArrayCopy());

        if ($usageTransfer === null) {
            return $promptResponseTransfer;
        }

        $promptMessageTransfer = $promptResponseTransfer->getMessage() ?? new PromptMessageTransfer();

        return $promptResponseTransfer->setMessage($promptMessageTransfer->setUsage($usageTransfer));
    }

    protected function expandPromptResponseWithProviderContext(
        PromptResponseTransfer $promptResponseTransfer,
        AiConfigurationTransfer $aiConfigurationTransfer,
        float $startedAt
    ): PromptResponseTransfer {
        $model = $aiConfigurationTransfer->getProviderConfig()[static::PROVIDER_CONFIG_KEY_MODEL] ?? null;

        return $promptResponseTransfer
            ->setProvider($aiConfigurationTransfer->getProviderName())
            ->setModel(is_scalar($model) ? (string)$model : null)
            ->setInferenceTimeMs((int)round((microtime(true) - $startedAt) * 1000));
    }
}
