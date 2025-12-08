<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\AWS\BedrockRuntime;
use NeuronAI\Providers\Deepseek\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HttpClientOptions;
use NeuronAI\Providers\HuggingFace\HuggingFace;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\AzureOpenAI;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\XAI\Grok;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException;

class ProviderResolver implements ProviderResolverInterface
{
    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_ANTHROPIC
     *
     * @var string
     */
    protected const PROVIDER_ANTHROPIC = 'anthropic';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_OPENAI
     *
     * @var string
     */
    protected const PROVIDER_OPENAI = 'openai';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_BEDROCK
     *
     * @var string
     */
    protected const PROVIDER_BEDROCK = 'bedrock';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_DEEPSEEK
     *
     * @var string
     */
    protected const PROVIDER_DEEPSEEK = 'deepseek';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_GEMINI
     *
     * @var string
     */
    protected const PROVIDER_GEMINI = 'gemini';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_HUGGINGFACE
     *
     * @var string
     */
    protected const PROVIDER_HUGGINGFACE = 'huggingface';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_MISTRAL
     *
     * @var string
     */
    protected const PROVIDER_MISTRAL = 'mistral';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_OLLAMA
     *
     * @var string
     */
    protected const PROVIDER_OLLAMA = 'ollama';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_GROK
     *
     * @var string
     */
    protected const PROVIDER_GROK = 'grok';

    /**
     * @see \Spryker\Shared\AiFoundation\AiFoundationConstants::PROVIDER_AZURE_OPEN_AI
     *
     * @var string
     */
    protected const PROVIDER_AZURE_OPEN_AI = 'azureopenai';

    /**
     * @var list<string>
     */
    protected const HTTP_CLIENT_OPTIONS_PROVIDERS = [
        self::PROVIDER_OPENAI,
        self::PROVIDER_ANTHROPIC,
        self::PROVIDER_DEEPSEEK,
        self::PROVIDER_HUGGINGFACE,
        self::PROVIDER_MISTRAL,
        self::PROVIDER_OLLAMA,
        self::PROVIDER_GROK,
        self::PROVIDER_AZURE_OPEN_AI,
        self::PROVIDER_GEMINI,
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function resolve(string $providerName, array $config): AIProviderInterface
    {
        $providerClass = $this->getProviderClass($providerName);

        return $this->createProviderInstance($providerClass, $config, $providerName);
    }

    /**
     * @throws \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
     *
     * @return class-string<\NeuronAI\Providers\AIProviderInterface>
     */
    protected function getProviderClass(string $providerName): string
    {
        $providerClassMap = $this->getAiProvidersClassmap();

        if (!isset($providerClassMap[$providerName])) {
            throw new NeuronAiConfigurationException(
                sprintf('Unknown provider type: %s', $providerName),
            );
        }

        return $providerClassMap[$providerName];
    }

    /**
     * @return array<string, class-string<\NeuronAI\Providers\AIProviderInterface>>
     */
    protected function getAiProvidersClassmap(): array
    {
        return [
            static::PROVIDER_ANTHROPIC => Anthropic::class,
            static::PROVIDER_OPENAI => OpenAI::class,
            static::PROVIDER_BEDROCK => BedrockRuntime::class,
            static::PROVIDER_DEEPSEEK => Deepseek::class,
            static::PROVIDER_GEMINI => Gemini::class,
            static::PROVIDER_HUGGINGFACE => HuggingFace::class,
            static::PROVIDER_MISTRAL => Mistral::class,
            static::PROVIDER_OLLAMA => Ollama::class,
            static::PROVIDER_GROK => Grok::class,
            static::PROVIDER_AZURE_OPEN_AI => AzureOpenAI::class,
        ];
    }

    /**
     * @param class-string<\NeuronAI\Providers\AIProviderInterface> $providerClass
     * @param array<string, mixed> $config
     */
    protected function createProviderInstance(string $providerClass, array $config, string $providerName): AIProviderInterface
    {
        $config = $this->expandProviderConfig($config, $providerName);

        return new $providerClass(...$config);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function expandProviderConfig(array $config, string $providerName): array
    {
        $config = $this->expandBedrockRuntimeClientConfig($config, $providerName);
        $config = $this->expandHttpClientOptionsConfig($config, $providerName);

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function expandBedrockRuntimeClientConfig(array $config, string $providerName): array
    {
        if ($providerName !== static::PROVIDER_BEDROCK) {
            return $config;
        }

        $awsConfig = $this->buildAwsConfig($config['bedrockRuntimeClient']);
        $config['bedrockRuntimeClient'] = new BedrockRuntimeClient($awsConfig);

        return $config;
    }

    /**
     * @param array<string, mixed> $bedrockRuntimeClientConfig
     *
     * @return array<string, mixed>
     */
    protected function buildAwsConfig(array $bedrockRuntimeClientConfig): array
    {
        $awsConfig = [
            'region' => $bedrockRuntimeClientConfig['region'],
            'version' => $bedrockRuntimeClientConfig['version'] ?? 'latest',
        ];

        if (isset($bedrockRuntimeClientConfig['credentials'])) {
            $awsConfig['credentials'] = $bedrockRuntimeClientConfig['credentials'];
        }

        return $awsConfig;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function expandHttpClientOptionsConfig(array $config, string $providerName): array
    {
        if (!in_array($providerName, static::HTTP_CLIENT_OPTIONS_PROVIDERS, true)) {
            return $config;
        }

        $httpClientOptionsConfig = $config['httpOptions'] ?? [];

        if (count($httpClientOptionsConfig) === 0) {
            return $config;
        }

        if (!$this->shouldCreateHttpClientOptions($httpClientOptionsConfig)) {
            return $config;
        }

        $config['httpOptions'] = $this->createHttpClientOptions($httpClientOptionsConfig);

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function shouldCreateHttpClientOptions(array $config): bool
    {
        return isset($config['timeout'])
            || isset($config['connectTimeout'])
            || isset($config['headers'])
            || isset($config['handler']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function createHttpClientOptions(array $config): HttpClientOptions
    {
        return new HttpClientOptions(
            timeout: $config['timeout'] ?? null,
            connectTimeout: $config['connectTimeout'] ?? null,
            headers: $config['headers'] ?? null,
            handler: $config['handler'] ?? null,
        );
    }
}
