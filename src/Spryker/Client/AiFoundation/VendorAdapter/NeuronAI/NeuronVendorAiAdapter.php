<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI;

use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
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
     * @param array<string, array<string, mixed>> $aiConfigurations
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface> $aiToolSetPlugins
     */
    public function __construct(
        protected ProviderResolverInterface $providerResolver,
        protected NeuronAiMessageMapper $messageMapper,
        protected NeuronAiToolMapperInterface $toolMapper,
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

        $structuredSchema = $promptRequest->getStructuredMessage();

        if ($structuredSchema instanceof AbstractTransfer) {
            return $this->executeStructuredPrompt($provider, $message, $structuredSchema, $maxRetries);
        }

        return $this->executeRegularPrompt($provider, $message, $maxRetries);
    }

    /**
     * @param \NeuronAI\Providers\AIProviderInterface $provider
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param int $maxRetries
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function executeRegularPrompt(AIProviderInterface $provider, Message $message, int $maxRetries): PromptResponseTransfer
    {
        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $toolInvocationTransfers = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $conversationHistory = [$message];
            try {
                $response = $provider->chat([$message]);

                while ($response instanceof ToolCallMessage) {
                    $executedTools = $this->executeToolCalls($response);

                    $toolResultMessage = new ToolCallResultMessage($executedTools);
                    $conversationHistory[] = $response;
                    $conversationHistory[] = $toolResultMessage;

                    $mappedToolInvocations = $this->messageMapper->mapExecutedToolsToToolInvocations($executedTools);
                    $toolInvocationTransfers = array_merge($toolInvocationTransfers, $mappedToolInvocations);

                    $response = $provider->chat($conversationHistory);
                }

                $promptResponseTransfer = $this->messageMapper->mapProviderResponseToPromptResponse($response);
                $promptResponseTransfer->setIsSuccessful(true);

                break;
            } catch (Throwable $exception) {
                $exceptions[] = $exception;
            }
        }

        foreach ($toolInvocationTransfers as $toolInvocationTransfer) {
            $promptResponseTransfer->addToolInvocation($toolInvocationTransfer);
        }

        if ($promptResponseTransfer->getIsSuccessful() === null) {
            $promptResponseTransfer->setIsSuccessful(false);
        }

        foreach ($exceptions as $index => $exception) {
            $errorTransfer = (new ErrorTransfer())
                ->setMessage(sprintf(
                    'Attempt %d failed: %s',
                    $index + 1,
                    $exception->getMessage(),
                ));

            $promptResponseTransfer->addError($errorTransfer);
        }

        return $promptResponseTransfer;
    }

    /**
     * @param \NeuronAI\Providers\AIProviderInterface $provider
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $structuredSchema
     * @param int $maxRetries
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    protected function executeStructuredPrompt(
        AIProviderInterface $provider,
        Message $message,
        AbstractTransfer $structuredSchema,
        int $maxRetries,
    ): PromptResponseTransfer {
        $structuredResponseFormat = $this->messageMapper->mapTransferToStructuredResponseFormat($structuredSchema);
        $structuredSchemaClass = get_class($structuredSchema);

        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $responseContents = [];
        $toolInvocationTransfers = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = null;

            $conversationHistory = [$message];
            try {
                $response = $provider->structured($conversationHistory, $structuredSchemaClass, $structuredResponseFormat);

                while ($response instanceof ToolCallMessage) {
                    $executedTools = $this->executeToolCalls($response);

                    $toolResultMessage = new ToolCallResultMessage($executedTools);
                    $conversationHistory[] = $response;
                    $conversationHistory[] = $toolResultMessage;

                    $mappedToolInvocations = $this->messageMapper->mapExecutedToolsToToolInvocations($executedTools);
                    $toolInvocationTransfers = array_merge($toolInvocationTransfers, $mappedToolInvocations);

                    $response = $provider->structured($conversationHistory, $structuredSchemaClass, $structuredResponseFormat);
                }

                $responseContents[] = $response->getContent() ?? 'No content';

                $structuredTransfer = $this->messageMapper->mapProviderStructuredResponseToTransfer($response, $structuredSchema);

                $promptResponseTransfer->setStructuredMessage($structuredTransfer);
                $promptResponseTransfer->setIsSuccessful(true);

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

        foreach ($toolInvocationTransfers as $toolInvocationTransfer) {
            $promptResponseTransfer->addToolInvocation($toolInvocationTransfer);
        }

        if ($promptResponseTransfer->getIsSuccessful() === null) {
            $promptResponseTransfer->setIsSuccessful(false);
        }

        foreach ($exceptions as $index => $exception) {
            $errorTransfer = (new ErrorTransfer())
                ->setMessage(sprintf(
                    'Attempt %d failed: %s. Response content: %s',
                    $index + 1,
                    $exception->getMessage(),
                    $responseContents[$index] ?? 'No content available',
                ))
                ->setEntityIdentifier(get_class($structuredSchema));

            $promptResponseTransfer->addError($errorTransfer);
        }

        return $promptResponseTransfer;
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
     * @throws \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
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
     * @throws \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
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
     * @param array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface> $toolSets
     *
     * @return array<\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface>
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
}
