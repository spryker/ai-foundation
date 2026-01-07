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
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
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
     */
    public function __construct(
        protected ProviderResolverInterface $providerResolver,
        protected NeuronAiMessageMapper $messageMapper,
        protected array $aiConfigurations,
    ) {
    }

    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer
    {
        $resolvedAiConfiguration = $this->resolveAiConfiguration($promptRequest);
        $provider = $this->resolveProvider($resolvedAiConfiguration);

        if (isset($resolvedAiConfiguration[static::AI_CONFIG_SYSTEM_PROMPT])) {
            $provider->systemPrompt($resolvedAiConfiguration[static::AI_CONFIG_SYSTEM_PROMPT]);
        }

        $message = $this->messageMapper->mapPromptMessageToProviderMessage($promptRequest->getPromptMessageOrFail());
        $maxRetries = $promptRequest->getMaxRetries() ?? 1;

        $structuredSchema = $promptRequest->getStructuredMessage();

        if ($structuredSchema instanceof AbstractTransfer) {
            return $this->executeStructuredPrompt($provider, $message, $structuredSchema, $maxRetries);
        }

        return $this->executeRegularPrompt($provider, $message, $maxRetries);
    }

    protected function executeRegularPrompt(AIProviderInterface $provider, Message $message, int $maxRetries): PromptResponseTransfer
    {
        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $provider->chat([$message]);

                $promptResponseTransfer = $this->messageMapper->mapProviderResponseToPromptResponse($response);
                $promptResponseTransfer->setIsSuccessful(true);

                break;
            } catch (Throwable $exception) {
                $exceptions[] = $exception;
            }
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

    protected function executeStructuredPrompt(
        AIProviderInterface $provider,
        Message $message,
        AbstractTransfer $structuredSchema,
        int $maxRetries,
    ): PromptResponseTransfer {
        $structuredResponseFormat = $this->messageMapper->mapTransferToStructuredResponseFormat($structuredSchema);

        $promptResponseTransfer = new PromptResponseTransfer();
        $exceptions = [];
        $responseContents = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = null;

            try {
                $response = $provider->structured([$message], get_class($structuredSchema), $structuredResponseFormat);

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
}
