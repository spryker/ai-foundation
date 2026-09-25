<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\Exception\AiFoundationConfigurationResolverException;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class AiFoundationConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Defines the default maximum context window size for conversation history.
     * - Value is expressed in tokens.
     * - Represents the maximum number of tokens to retain in conversation history storage per conversation.
     * - When the conversation history exceeds this limit, the oldest messages are automatically pruned to stay within the limit.
     * - This limit is applied per conversation, not globally across all conversations.
     * - Helps prevent storage overflow and ensures conversation history fits within AI model context limits.
     * - The serialized history measures roughly 12 bytes per counted token, because the envelope (message ids,
     *   roles, tool call arguments and results, usage blocks, escaping) is persisted but not token counted, so
     *   this window is persisted into a `LONGTEXT` column rather than the `TEXT` one it would overflow.
     */
    protected const int DEFAULT_CONVERSATION_HISTORY_CONTEXT_WINDOW = 50000;

    protected const DEFAULT_INITIAL_STATE = 'new';

    protected const int DEFAULT_AI_INTERACTION_LOG_DATE_RANGE_DAYS = 7;

    protected const string PRICE_CONFIGURATION_KEY_TEMPLATE = 'ai_vendor:%s:general:model_prices';

    protected const int COST_ROUNDING_PRECISION_ROW = 4;

    protected const int COST_ROUNDING_PRECISION_AGGREGATE = 2;

    protected const int TOKENS_PER_PRICE_UNIT = 1000000;

    protected const string PROVIDER_TAB_OPENAI = 'openai';

    protected const string PROVIDER_TAB_ANTHROPIC = 'anthropic';

    protected const string PROVIDER_TAB_AWS = 'aws';

    /**
     * @api
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAiConfigurations(): array
    {
        $configurations = $this->get(AiFoundationConstants::AI_CONFIGURATIONS);

        return $this->resolveConfigurationReferences($configurations);
    }

    /**
     * Specification:
     * - Returns the maximum context window size for conversation history.
     * - Value is expressed in tokens.
     * - Represents the maximum number of tokens to retain in conversation history storage per conversation.
     * - When the conversation history exceeds this limit, the oldest messages are automatically pruned to stay within the limit.
     * - This limit is applied per conversation, not globally across all conversations.
     * - Helps prevent storage overflow and ensures conversation history fits within AI model context limits.
     *
     * @api
     */
    public function getConversationHistoryContextWindow(): int
    {
        return $this->get(AiFoundationConstants::CONVERSATION_HISTORY_CONTEXT_WINDOW, static::DEFAULT_CONVERSATION_HISTORY_CONTEXT_WINDOW);
    }

    /**
     * Specification:
     * - Returns the AI configuration names a Client-layer caller is allowed to resolve through the gateway.
     * - Returns an empty array by default, so no configuration is resolvable until a project opts one in.
     * - A configuration name absent from this list is rejected with an unsuccessful response.
     *
     * @api
     *
     * @return array<string>
     */
    public function getClientResolvableAiConfigurationNames(): array
    {
        return [];
    }

    /**
     * @api
     */
    public function getAiWorkflowStateMachineName(): string
    {
        return AiFoundationConstants::AI_WORKFLOW_STATE_MACHINE_NAME;
    }

    /**
     * @api
     */
    public function getAiInteractionLogDefaultDateRangeDays(): int
    {
        return static::DEFAULT_AI_INTERACTION_LOG_DATE_RANGE_DAYS;
    }

    /**
     * Specification:
     * - Returns a list of active AI workflow processes for the project.
     * - Expected format: ['IntelligentTask01', 'IntelligentTask02'].
     *
     * @api
     *
     * @return array<string>
     */
    public function getAiWorkflowActiveProcesses(): array
    {
        return [];
    }

    /**
     * @api
     */
    public function getAiWorkflowInitialStateForProcess(string $process): string
    {
        return $this->getAiWorkflowInitialStateMapForProcess()[$process] ?? static::DEFAULT_INITIAL_STATE;
    }

    /**
     * Specification:
     * - Returns a map of process names to their initial states for the project.
     * - Expected format: ['IntelligentTask01' => 'new'].
     *
     * @return array<string, string>
     */
    protected function getAiWorkflowInitialStateMapForProcess(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the number of decimal places to round a single interaction's estimated cost to for display.
     *
     * @api
     */
    public function getCostRoundingPrecisionForRow(): int
    {
        return static::COST_ROUNDING_PRECISION_ROW;
    }

    /**
     * Specification:
     * - Returns the number of decimal places to round aggregated estimated cost figures to for display.
     *
     * @api
     */
    public function getCostRoundingPrecisionForAggregate(): int
    {
        return static::COST_ROUNDING_PRECISION_AGGREGATE;
    }

    /**
     * Specification:
     * - Returns the number of tokens a configured token price applies to (price is expressed per this many tokens).
     *
     * @api
     */
    public function getTokensPerPriceUnit(): int
    {
        return static::TOKENS_PER_PRICE_UNIT;
    }

    /**
     * Specification:
     * - Reads the configured token prices for the given AI Vendor provider tab (e.g. "openai", "anthropic", "aws").
     * - The price setting is a JSON object keyed by model identifier, each holding "input" and "output" prices in USD per 1,000,000 tokens.
     * - Returns a map of model identifier to its decoded price entry; returns an empty array when nothing is configured, the value is not valid JSON, or the Configuration feature is not installed.
     *
     * @api
     *
     * @return array<string, mixed>
     */
    public function getAiModelPricesForProviderTab(string $providerTab): array
    {
        $rawValue = $this->getModuleConfig(sprintf(static::PRICE_CONFIGURATION_KEY_TEMPLATE, $providerTab));

        return $this->decodeModelPrices($rawValue);
    }

    /**
     * Specification:
     * - Maps a logged interaction's provider name to the AI Vendor configuration tab that holds its model prices.
     * - The AWS Bedrock provider is logged as "bedrock" while its prices live under the "aws" configuration tab.
     * - Providers absent from this map have no configured prices and are treated as unpriced ("N/A").
     * - Override at the project level to price additional providers.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getProviderPricingTabMap(): array
    {
        return [
            AiFoundationConstants::PROVIDER_OPENAI => static::PROVIDER_TAB_OPENAI,
            AiFoundationConstants::PROVIDER_ANTHROPIC => static::PROVIDER_TAB_ANTHROPIC,
            AiFoundationConstants::PROVIDER_BEDROCK => static::PROVIDER_TAB_AWS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeModelPrices(mixed $rawValue): array
    {
        if (is_array($rawValue)) {
            return $rawValue;
        }

        if (!is_string($rawValue) || $rawValue === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $configurations
     *
     * @throws \Spryker\Zed\AiFoundation\Exception\AiFoundationConfigurationResolverException
     *
     * @return array<string, mixed>
     */
    protected function resolveConfigurationReferences(array $configurations): array
    {
        $unresolvedReferences = [];
        $resolved = $this->doResolveConfigurationReferences($configurations, $unresolvedReferences);

        if ($unresolvedReferences === []) {
            return $resolved;
        }

        throw new AiFoundationConfigurationResolverException(
            sprintf(
                'Unable to resolve the following configuration references: "%s". '
                . 'Configuration references use the "%s" prefix and are resolved via \Spryker\Zed\Configuration\Business\ConfigurationFacade::getConfigurationValue(). '
                . 'Ensure the referenced keys are defined in your configuration (e.g. config/Shared/*.php).',
                implode('", "', $unresolvedReferences),
                AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX,
            ),
        );
    }

    /**
     * @param array<string, mixed> $configurations
     * @param array<string> $unresolvedReferences
     *
     * @return array<string, mixed>
     */
    protected function doResolveConfigurationReferences(array $configurations, array &$unresolvedReferences): array
    {
        foreach ($configurations as $key => $value) {
            if (is_array($value)) {
                $configurations[$key] = $this->doResolveConfigurationReferences($value, $unresolvedReferences);

                continue;
            }

            if (!is_string($value) || !str_starts_with($value, AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX)) {
                continue;
            }

            $configurationKey = substr($value, strlen(AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX));
            $resolvedValue = $this->getModuleConfig($configurationKey);

            if ($resolvedValue === null) {
                $unresolvedReferences[] = $value;

                continue;
            }

            $configurations[$key] = $resolvedValue;
        }

        return $configurations;
    }
}
