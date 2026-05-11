<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI;

use ArrayObject;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolResultMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\VendorAdapterInterface;
use Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface;
use Throwable;

class NeuronVendorAiAdapter implements VendorAdapterInterface
{
    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::AI_PROVIDER_NAME
     *
     * @var string
     */
    protected const AI_PROVIDER_NAME = 'provider_name';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::AI_PROVIDER_CONFIG
     *
     * @var string
     */
    protected const AI_PROVIDER_CONFIG = 'provider_config';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::AI_CONFIG_SYSTEM_PROMPT
     *
     * @var string
     */
    protected const AI_CONFIG_SYSTEM_PROMPT = 'system_prompt';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::AI_CONFIGURATION_DEFAULT
     *
     * @var string
     */
    protected const AI_CONFIGURATION_DEFAULT = 'AI_CONFIGURATION_DEFAULT';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::AI_CONVERSATION_HISTORY_CONFIG
     *
     * @var string
     */
    protected const string AI_CONVERSATION_HISTORY_CONFIG = 'conversation_history';

    protected const string AI_PROVIDER_CONFIG_MODEL = 'model';

    /**
     * @param array<string, array<string, mixed>> $aiConfigurations
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface> $aiToolSetPlugins
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PostPromptPluginInterface> $postPromptPlugins
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface> $preToolCallPlugins
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface> $postToolCallPlugins
     */
    public function __construct(
        protected ProviderResolverInterface $providerResolver,
        protected NeuronAiMessageMapperInterface $messageMapper,
        protected NeuronAiToolMapperInterface $toolMapper,
        protected ChatHistoryResolverInterface $chatHistoryResolver,
        protected array $aiConfigurations,
        protected array $aiToolSetPlugins,
        protected array $postPromptPlugins,
        protected array $preToolCallPlugins = [],
        protected array $postToolCallPlugins = [],
    ) {
    }

    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer
    {
        $resolvedAiConfiguration = $this->resolveAiConfiguration($promptRequest);
        $provider = $this->resolveProvider($resolvedAiConfiguration);

        if (isset($resolvedAiConfiguration[static::AI_CONFIG_SYSTEM_PROMPT])) {
            $provider->systemPrompt($resolvedAiConfiguration[static::AI_CONFIG_SYSTEM_PROMPT]);
        }

        $provider = $this->setToolsToProvider($provider, $promptRequest);

        $message = $this->messageMapper->mapPromptMessageToProviderMessage($promptRequest->getPromptMessageOrFail());
        $maxRetries = $promptRequest->getMaxRetries() ?? 1;

        $chatHistoryConfig = $resolvedAiConfiguration[static::AI_CONVERSATION_HISTORY_CONFIG] ?? [];
        $chatHistory = $this->chatHistoryResolver->resolve($promptRequest->getConversationReference(), $chatHistoryConfig);

        $structuredSchema = $promptRequest->getStructuredMessage();

        $providerName = $resolvedAiConfiguration[static::AI_PROVIDER_NAME];
        $modelName = $resolvedAiConfiguration[static::AI_PROVIDER_CONFIG][static::AI_PROVIDER_CONFIG_MODEL] ?? null;
        $startTime = microtime(true);

        $promptResponseTransfer = $structuredSchema instanceof AbstractTransfer
            ? $this->executeStructuredPrompt($provider, $message, $structuredSchema, $maxRetries, $promptRequest, $chatHistory)
            : $this->executePlainPrompt($provider, $message, $maxRetries, $promptRequest, $chatHistory);

        $promptResponseTransfer = $promptResponseTransfer
            ->setProvider($providerName)
            ->setModel($modelName)
            ->setInferenceTimeMs((int)round((microtime(true) - $startTime) * 1000));

        foreach ($this->postPromptPlugins as $postPromptPlugin) {
            $postPromptPlugin->postPrompt($promptRequest, $promptResponseTransfer);
        }

        return $promptResponseTransfer;
    }

    protected function executePlainPrompt(
        AIProviderInterface $provider,
        Message $message,
        int $maxRetries,
        PromptRequestTransfer $promptRequestTransfer,
        ?ChatHistoryInterface $chatHistory = null,
    ): PromptResponseTransfer {
        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $conversationMessages = $this->prepareConversationMessages($chatHistory, $message);

            try {
                $response = $provider->chat(...$conversationMessages);

                while ($response instanceof ToolCallMessage) {
                    $conversationMessages = $this->processToolCallAndUpdateHistory(
                        $response,
                        $conversationMessages,
                        $promptRequestTransfer,
                    );

                    $response = $provider->chat(...$conversationMessages);
                }

                $promptResponseTransfer = $this->messageMapper->mapProviderResponseToPromptResponse($response);
                $promptResponseTransfer->setIsSuccessful(true);

                if ($chatHistory !== null) {
                    $this->persistConversationMessages($chatHistory, $conversationMessages, $response);
                }

                break;
            } catch (Throwable $exception) {
                $exceptions[] = $exception;
            }
        }

        return $this->finalizePromptResponse($promptResponseTransfer, $exceptions);
    }

    protected function executeStructuredPrompt(
        AIProviderInterface $provider,
        Message $message,
        AbstractTransfer $structuredSchema,
        int $maxRetries,
        PromptRequestTransfer $promptRequestTransfer,
        ?ChatHistoryInterface $chatHistory = null,
    ): PromptResponseTransfer {
        $structuredResponseFormat = $this->messageMapper->mapTransferToStructuredResponseFormat($structuredSchema);
        $structuredSchemaClass = get_class($structuredSchema);

        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $responseContents = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = null;

            $conversationMessages = $this->prepareConversationMessages($chatHistory, $message);

            try {
                $response = $provider->structured($conversationMessages, $structuredSchemaClass, $structuredResponseFormat);

                while ($response instanceof ToolCallMessage) {
                    $conversationMessages = $this->processToolCallAndUpdateHistory(
                        $response,
                        $conversationMessages,
                        $promptRequestTransfer,
                    );

                    $response = $provider->structured($conversationMessages, $structuredSchemaClass, $structuredResponseFormat);
                }

                $responseContents[] = $response->getContent() ?? 'No content';

                $structuredTransfer = $this->messageMapper->mapProviderStructuredResponseToTransfer($response, $structuredSchema);

                $promptResponseTransfer->setStructuredMessage($structuredTransfer);
                $promptResponseTransfer->setMessage(
                    $this->messageMapper->mapProviderResponseToPromptResponse($response)->getMessage(),
                );
                $promptResponseTransfer->setIsSuccessful(true);

                if ($chatHistory !== null) {
                    $this->persistConversationMessages($chatHistory, $conversationMessages, $response);
                }

                break;
            } catch (Throwable $exception) {
                $exceptions[] = $exception;

                if ($response !== null) {
                    $responseContents[] = $response->getContent() ?? 'No content';
                } else {
                    $responseContents[] = null;
                }
            }
        }

        return $this->finalizePromptResponse($promptResponseTransfer, $exceptions, $responseContents, get_class($structuredSchema));
    }

    /**
     * @param \NeuronAI\Chat\History\ChatHistoryInterface|null $chatHistory
     * @param \NeuronAI\Chat\Messages\Message $message
     *
     * @return array<\NeuronAI\Chat\Messages\Message>
     */
    protected function prepareConversationMessages(?ChatHistoryInterface $chatHistory, Message $message): array
    {
        $conversationMessages = $chatHistory !== null ? $chatHistory->getMessages() : [];
        $conversationMessages[] = $message;

        return $conversationMessages;
    }

    /**
     * @param \NeuronAI\Chat\History\ChatHistoryInterface $chatHistory
     * @param array<\NeuronAI\Chat\Messages\Message> $conversationMessages
     * @param \NeuronAI\Chat\Messages\Message $response
     *
     * @return void
     */
    protected function persistConversationMessages(
        ChatHistoryInterface $chatHistory,
        array $conversationMessages,
        Message $response
    ): void {
        $initialChatMessagesCount = count($chatHistory->getMessages());
        $actualConversationMessagesCount = count($conversationMessages);

        for ($i = $initialChatMessagesCount; $i < $actualConversationMessagesCount; $i++) {
            $chatHistory->addMessage($conversationMessages[$i]);
        }

        $chatHistory->addMessage($response);
    }

    /**
     * @param array<string, mixed> $aiConfigConfiguration
     */
    protected function resolveProvider(array $aiConfigConfiguration): AIProviderInterface
    {
        return $this->providerResolver->resolve(
            $aiConfigConfiguration[static::AI_PROVIDER_NAME],
            $aiConfigConfiguration[static::AI_PROVIDER_CONFIG],
        );
    }

    /**
     * @throws \Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
     *
     * @return array<string, mixed>
     */
    protected function resolveAiConfiguration(PromptRequestTransfer $promptRequest): array
    {
        $aiConfigurationName = $promptRequest->getAiConfigurationName() ?? static::AI_CONFIGURATION_DEFAULT;

        if (!isset($this->aiConfigurations[$aiConfigurationName])) {
            throw new NeuronAiConfigurationException(sprintf('AI configuration "%s" is not configured.', $aiConfigurationName));
        }

        $resolvedAiConfiguration = $this->aiConfigurations[$aiConfigurationName];

        $this->assertAiConfiguration($aiConfigurationName, $resolvedAiConfiguration);

        return $resolvedAiConfiguration;
    }

    /**
     * @param array<string, mixed> $aiConfiguration
     *
     * @throws \Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
     */
    protected function assertAiConfiguration(string $aiConfigurationName, array $aiConfiguration): void
    {
        $requiredKeys = [
            static::AI_PROVIDER_NAME,
            static::AI_PROVIDER_CONFIG,
        ];

        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $aiConfiguration)) {
                throw new NeuronAiConfigurationException(
                    sprintf('AI configuration "%s" is missing required configuration key "%s".', $aiConfigurationName, $requiredKey),
                );
            }

            if (empty($aiConfiguration[$requiredKey])) {
                throw new NeuronAiConfigurationException(
                    sprintf('AI configuration "%s" configuration key "%s" cannot be empty.', $aiConfigurationName, $requiredKey),
                );
            }
        }
    }

    protected function setToolsToProvider(
        AIProviderInterface $provider,
        PromptRequestTransfer $promptRequest,
    ): AIProviderInterface {
        $toolSetNames = $promptRequest->getToolSetNames();

        $matchingToolSets = array_filter(
            $this->aiToolSetPlugins,
            static fn (ToolSetPluginInterface $toolSet) => in_array($toolSet->getName(), $toolSetNames, true),
        );

        if (count($matchingToolSets) === 0) {
            return $provider;
        }

        $tools = $this->extractToolsFromToolSets($matchingToolSets);

        $neuronTools = $this->toolMapper->mapToolsToNeuronTools($tools);
        $provider->setTools($neuronTools);

        return $provider;
    }

    /**
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface> $toolSets
     *
     * @return array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolPluginInterface>
     */
    protected function extractToolsFromToolSets(array $toolSets): array
    {
        $tools = [];

        foreach ($toolSets as $toolSet) {
            $tools = array_merge($tools, $toolSet->getTools());
        }

        return $tools;
    }

    /**
     * @return array<\NeuronAI\Tools\ToolInterface>
     */
    protected function executeToolCalls(
        ToolCallMessage $toolCallMessage,
        PromptRequestTransfer $promptRequestTransfer,
    ): array {
        $tools = $toolCallMessage->getTools();

        foreach ($tools as $tool) {
            $aiToolCallTransfer = $this->createAiToolCallTransfer($tool, $promptRequestTransfer);

            $aiToolCallTransfer = $this->executePreToolCallPlugins($aiToolCallTransfer);

            if ($aiToolCallTransfer->getIsExecutionAllowed() !== false) {
                $tool->execute();
            }

            $aiToolCallTransfer->setToolResult($tool->getResult());

            $this->executePostToolCallPlugins($aiToolCallTransfer);
        }

        return $tools;
    }

    protected function createAiToolCallTransfer(
        ToolInterface $tool,
        PromptRequestTransfer $promptRequestTransfer,
    ): AiToolCallTransfer {
        return (new AiToolCallTransfer())
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

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $conversationMessages
     *
     * @return array<\NeuronAI\Chat\Messages\Message>
     */
    protected function processToolCallAndUpdateHistory(
        ToolCallMessage $response,
        array $conversationMessages,
        PromptRequestTransfer $promptRequestTransfer,
    ): array {
        $executedTools = $this->executeToolCalls($response, $promptRequestTransfer);

        $toolResultMessage = new ToolResultMessage($executedTools);
        $conversationMessages[] = $response;
        $conversationMessages[] = $toolResultMessage;

        return $conversationMessages;
    }

    /**
     * @param array<\Throwable> $exceptions
     * @param array<string|null> $responseContents
     */
    protected function finalizePromptResponse(
        PromptResponseTransfer $promptResponseTransfer,
        array $exceptions,
        array $responseContents = [],
        ?string $entityIdentifier = null,
    ): PromptResponseTransfer {
        if ($promptResponseTransfer->getIsSuccessful() === null) {
            $promptResponseTransfer->setIsSuccessful(false);
        }

        return $this->addErrorsToResponse($promptResponseTransfer, $exceptions, $responseContents, $entityIdentifier);
    }

    /**
     * @param \Generated\Shared\Transfer\PromptResponseTransfer $promptResponseTransfer
     * @param array<\Throwable> $exceptions
     * @param array<string|null> $responseContents
     * @param string|null $entityIdentifier
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function addErrorsToResponse(
        PromptResponseTransfer $promptResponseTransfer,
        array $exceptions,
        array $responseContents = [],
        ?string $entityIdentifier = null,
    ): PromptResponseTransfer {
        foreach ($exceptions as $index => $exception) {
            $message = $this->buildErrorMessage($index, $exception, $responseContents);

            $errorTransfer = (new ErrorTransfer())->setMessage($message);

            if ($entityIdentifier !== null) {
                $errorTransfer->setEntityIdentifier($entityIdentifier);
            }

            $promptResponseTransfer->addError($errorTransfer);
        }

        return $promptResponseTransfer;
    }

    /**
     * @param array<string|null> $responseContents
     */
    protected function buildErrorMessage(int $index, Throwable $exception, array $responseContents): string
    {
        $baseMessage = sprintf('Attempt %d failed: %s', $index + 1, $exception->getMessage());

        if (count($responseContents) === 0) {
            return $baseMessage;
        }

        return sprintf(
            '%s. Response content: %s',
            $baseMessage,
            $responseContents[$index] ?? 'No content available',
        );
    }

    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer {
        $conversationHistoryCollectionTransfer = new ConversationHistoryCollectionTransfer();

        $conversationReferences = $conversationHistoryCriteriaTransfer
            ->getConversationHistoryConditions()?->getConversationReferences() ?? [];

        foreach ($conversationReferences as $conversationReference) {
            $chatHistory = $this->chatHistoryResolver->resolve($conversationReference, []);

            $promptMessages = $chatHistory !== null ? $this->messageMapper->mapProviderMessagesToPromptMessages($chatHistory->getMessages()) : [];

            $conversationHistoryCollectionTransfer->addConversationHistory(
                (new ConversationHistoryTransfer())
                    ->setConversationReference($conversationReference)
                    ->setMessages(new ArrayObject($promptMessages)),
            );
        }

        return $conversationHistoryCollectionTransfer;
    }
}
