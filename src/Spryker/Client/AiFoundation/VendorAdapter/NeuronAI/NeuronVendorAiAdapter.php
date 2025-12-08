<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;

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

        $response = $provider->chat([$message]);

        return $this->messageMapper->mapProviderResponseToPromptResponse($response);
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
