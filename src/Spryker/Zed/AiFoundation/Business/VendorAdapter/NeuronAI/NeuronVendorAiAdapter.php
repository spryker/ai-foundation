<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI;

use ArrayObject;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
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

    /**
     * @param array<string, array<string, mixed>> $aiConfigurations
     * @param array<\Spryker\Zed\AiFoundation\Dependency\Tools\ToolSetPluginInterface> $aiToolSetPlugins
     */
    public function __construct(
        protected ProviderResolverInterface $providerResolver,
        protected NeuronAiMessageMapper $messageMapper,
        protected NeuronAiToolMapperInterface $toolMapper,
        protected ChatHistoryResolverInterface $chatHistoryResolver,
        protected array $aiConfigurations,
        protected array $aiToolSetPlugins = [],
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
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

        if ($structuredSchema instanceof AbstractTransfer) {
            return $this->executeStructuredPrompt($provider, $message, $structuredSchema, $maxRetries, $chatHistory);
        }

        return $this->executePlainPrompt($provider, $message, $maxRetries, $chatHistory);
    }

    /**
     * @param \NeuronAI\Providers\AIProviderInterface $provider
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param int $maxRetries
     * @param \NeuronAI\Chat\History\ChatHistoryInterface|null $chatHistory
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function executePlainPrompt(
        AIProviderInterface $provider,
        Message $message,
        int $maxRetries,
        ?ChatHistoryInterface $chatHistory = null,
    ): PromptResponseTransfer {
        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $toolInvocationTransfers = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $conversationMessages = $this->prepareConversationMessages($chatHistory, $message);

            try {
                $response = $provider->chat($conversationMessages);

                while ($response instanceof ToolCallMessage) {
                    [$conversationMessages, $toolInvocationTransfers] = $this->processToolCallAndUpdateHistory(
                        $response,
                        $conversationMessages,
                        $toolInvocationTransfers,
                    );

                    $response = $provider->chat($conversationMessages);
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

        return $this->finalizePromptResponse($promptResponseTransfer, $toolInvocationTransfers, $exceptions);
    }

    /**
     * @param \NeuronAI\Providers\AIProviderInterface $provider
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $structuredSchema
     * @param int $maxRetries
     * @param \NeuronAI\Chat\History\ChatHistoryInterface|null $chatHistory
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function executeStructuredPrompt(
        AIProviderInterface $provider,
        Message $message,
        AbstractTransfer $structuredSchema,
        int $maxRetries,
        ?ChatHistoryInterface $chatHistory = null,
    ): PromptResponseTransfer {
        $structuredResponseFormat = $this->messageMapper->mapTransferToStructuredResponseFormat($structuredSchema);
        $structuredSchemaClass = get_class($structuredSchema);

        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $responseContents = [];
        $toolInvocationTransfers = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = null;

            $conversationMessages = $this->prepareConversationMessages($chatHistory, $message);

            try {
                $response = $provider->structured($conversationMessages, $structuredSchemaClass, $structuredResponseFormat);

                while ($response instanceof ToolCallMessage) {
                    [$conversationMessages, $toolInvocationTransfers] = $this->processToolCallAndUpdateHistory(
                        $response,
                        $conversationMessages,
                        $toolInvocationTransfers,
                    );

                    $response = $provider->structured($conversationMessages, $structuredSchemaClass, $structuredResponseFormat);
                }

                $responseContents[] = $response->getContent() ?? 'No content';

                $structuredTransfer = $this->messageMapper->mapProviderStructuredResponseToTransfer($response, $structuredSchema);

                $promptResponseTransfer->setStructuredMessage($structuredTransfer);
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

        return $this->finalizePromptResponse($promptResponseTransfer, $toolInvocationTransfers, $exceptions, $responseContents, get_class($structuredSchema));
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

    /**
     * @param \NeuronAI\Providers\AIProviderInterface $provider
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @return \NeuronAI\Providers\AIProviderInterface
     */
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
     * @param \NeuronAI\Chat\Messages\ToolCallMessage $toolCallMessage
     *
     * @return array<\NeuronAI\Tools\ToolInterface>
     */
    protected function executeToolCalls(ToolCallMessage $toolCallMessage): array
    {
        $tools = $toolCallMessage->getTools();

        foreach ($tools as $tool) {
            $tool->execute();
        }

        return $tools;
    }

    /**
     * @param \NeuronAI\Chat\Messages\ToolCallMessage $response
     * @param array<\NeuronAI\Chat\Messages\Message> $conversationMessages
     * @param array<\Generated\Shared\Transfer\ToolInvocationTransfer> $toolInvocationTransfers
     *
     * @return array{array<\NeuronAI\Chat\Messages\Message>, array<\Generated\Shared\Transfer\ToolInvocationTransfer>}
     */
    protected function processToolCallAndUpdateHistory(
        ToolCallMessage $response,
        array $conversationMessages,
        array $toolInvocationTransfers,
    ): array {
        $executedTools = $this->executeToolCalls($response);

        $toolResultMessage = new ToolCallResultMessage($executedTools);
        $conversationMessages[] = $response;
        $conversationMessages[] = $toolResultMessage;

        $mappedToolInvocations = $this->messageMapper->mapExecutedToolsToToolInvocations($executedTools);
        $toolInvocationTransfers = array_merge($toolInvocationTransfers, $mappedToolInvocations);

        return [$conversationMessages, $toolInvocationTransfers];
    }

    /**
     * @param \Generated\Shared\Transfer\PromptResponseTransfer $promptResponseTransfer
     * @param array<\Generated\Shared\Transfer\ToolInvocationTransfer> $toolInvocationTransfers
     * @param array<\Throwable> $exceptions
     * @param array<string|null> $responseContents
     * @param string|null $entityIdentifier
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function finalizePromptResponse(
        PromptResponseTransfer $promptResponseTransfer,
        array $toolInvocationTransfers,
        array $exceptions,
        array $responseContents = [],
        ?string $entityIdentifier = null,
    ): PromptResponseTransfer {
        foreach ($toolInvocationTransfers as $toolInvocationTransfer) {
            $promptResponseTransfer->addToolInvocation($toolInvocationTransfer);
        }

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
