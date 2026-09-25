<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\CommandInterface;
use Aws\Middleware;
use Aws\Token\BedrockTokenProvider;
use Aws\Token\TokenSource;
use NeuronAI\HttpClient\GuzzleHttpClient;
use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\AWS\BedrockRuntime;
use NeuronAI\Providers\Deepseek\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HuggingFace\HuggingFace;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\AzureOpenAI;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\OpenAI\Responses\OpenAIResponses;
use NeuronAI\Providers\XAI\Grok;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Throwable;

abstract class AbstractProviderResolver
{
    /**
     * @var list<string>
     */
    protected const array HTTP_CLIENT_OPTIONS_PROVIDERS = [
        AiFoundationConstants::PROVIDER_OPENAI,
        AiFoundationConstants::PROVIDER_ANTHROPIC,
        AiFoundationConstants::PROVIDER_DEEPSEEK,
        AiFoundationConstants::PROVIDER_HUGGINGFACE,
        AiFoundationConstants::PROVIDER_MISTRAL,
        AiFoundationConstants::PROVIDER_OLLAMA,
        AiFoundationConstants::PROVIDER_GROK,
        AiFoundationConstants::PROVIDER_AZURE_OPEN_AI,
        AiFoundationConstants::PROVIDER_GEMINI,
        AiFoundationConstants::PROVIDER_OPENAI_RESPONSES,
    ];

    protected const float DEFAULT_HTTP_CLIENT_TIMEOUT = 60.0;

    protected const float DEFAULT_HTTP_CLIENT_CONNECT_TIMEOUT = 10.0;

    protected const string CONFIG_KEY_ADDITIONAL_MODEL_REQUEST_FIELDS = 'additionalModelRequestFields';

    protected const string MIDDLEWARE_NAME_ADDITIONAL_MODEL_REQUEST_FIELDS = 'spryker.ai_foundation.additional_model_request_fields';

    abstract protected function createProviderNotResolvableException(string $message): Throwable;

    /**
     * @param array<string, mixed> $config
     */
    public function resolve(string $providerName, array $config): AIProviderInterface
    {
        $providerClass = $this->getProviderClass($providerName);

        return $this->createProviderInstance($providerClass, $config, $providerName);
    }

    /**
     * @throws \Throwable
     *
     * @return class-string<\NeuronAI\Providers\AIProviderInterface>
     */
    protected function getProviderClass(string $providerName): string
    {
        $providerClassMap = $this->getAiProvidersClassmap();

        if (!isset($providerClassMap[$providerName])) {
            throw $this->createProviderNotResolvableException(
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
            AiFoundationConstants::PROVIDER_ANTHROPIC => Anthropic::class,
            AiFoundationConstants::PROVIDER_OPENAI => OpenAI::class,
            AiFoundationConstants::PROVIDER_BEDROCK => BedrockRuntime::class,
            AiFoundationConstants::PROVIDER_DEEPSEEK => Deepseek::class,
            AiFoundationConstants::PROVIDER_GEMINI => Gemini::class,
            AiFoundationConstants::PROVIDER_HUGGINGFACE => HuggingFace::class,
            AiFoundationConstants::PROVIDER_MISTRAL => Mistral::class,
            AiFoundationConstants::PROVIDER_OLLAMA => Ollama::class,
            AiFoundationConstants::PROVIDER_GROK => Grok::class,
            AiFoundationConstants::PROVIDER_AZURE_OPEN_AI => AzureOpenAI::class,
            AiFoundationConstants::PROVIDER_OPENAI_RESPONSES => OpenAIResponses::class,
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
        if ($providerName !== AiFoundationConstants::PROVIDER_BEDROCK) {
            return $config;
        }

        $awsConfig = $this->buildAwsConfig($config['bedrockRuntimeClient']);
        $bedrockRuntimeClient = new BedrockRuntimeClient($awsConfig);

        $this->addAdditionalModelRequestFieldsMiddleware(
            $bedrockRuntimeClient,
            $config[static::CONFIG_KEY_ADDITIONAL_MODEL_REQUEST_FIELDS] ?? [],
        );
        unset($config[static::CONFIG_KEY_ADDITIONAL_MODEL_REQUEST_FIELDS]);

        $config['bedrockRuntimeClient'] = $bedrockRuntimeClient;

        return $config;
    }

    /**
     * NeuronAI's `BedrockRuntime` cannot forward `additionalModelRequestFields` to Converse, so an init middleware adds them to every command of the client.
     *
     * @param array<string, mixed> $additionalModelRequestFields
     */
    protected function addAdditionalModelRequestFieldsMiddleware(
        BedrockRuntimeClient $bedrockRuntimeClient,
        array $additionalModelRequestFields
    ): void {
        if ($additionalModelRequestFields === []) {
            return;
        }

        $bedrockRuntimeClient->getHandlerList()->appendInit(
            Middleware::mapCommand(static function (CommandInterface $command) use ($additionalModelRequestFields): CommandInterface {
                $command[static::CONFIG_KEY_ADDITIONAL_MODEL_REQUEST_FIELDS] = $additionalModelRequestFields;

                return $command;
            }),
            static::MIDDLEWARE_NAME_ADDITIONAL_MODEL_REQUEST_FIELDS,
        );
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

        if (isset($bedrockRuntimeClientConfig['token']) && is_string($bedrockRuntimeClientConfig['token'])) {
            $awsConfig['token'] = BedrockTokenProvider::fromTokenValue(
                $bedrockRuntimeClientConfig['token'],
                TokenSource::BEARER_SERVICE_ENV_VARS,
            );
            $awsConfig['auth_scheme_preference'] = [BedrockTokenProvider::BEARER_AUTH];
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
        unset($config['httpOptions']);

        if (count($httpClientOptionsConfig) === 0) {
            return $config;
        }

        if (!$this->shouldCreateHttpClient($httpClientOptionsConfig)) {
            return $config;
        }

        $config['httpClient'] = $this->createHttpClient($httpClientOptionsConfig);

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function shouldCreateHttpClient(array $config): bool
    {
        return isset($config['timeout'])
            || isset($config['connectTimeout'])
            || isset($config['headers'])
            || isset($config['handler']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function createHttpClient(array $config): HttpClientInterface
    {
        return new GuzzleHttpClient(
            customHeaders: $config['headers'] ?? [],
            timeout: (float)($config['timeout'] ?? static::DEFAULT_HTTP_CLIENT_TIMEOUT),
            connectTimeout: (float)($config['connectTimeout'] ?? static::DEFAULT_HTTP_CLIENT_CONNECT_TIMEOUT),
            handler: $config['handler'] ?? null,
        );
    }
}
