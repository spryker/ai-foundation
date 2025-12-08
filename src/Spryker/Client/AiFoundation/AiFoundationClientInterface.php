<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface AiFoundationClientInterface
{
    /**
     * Specification:
     * - Sends a prompt request to the configured AI provider.
     * - Resolves AI configuration by name from `PromptRequestTransfer.aiConfigurationName`.
     * - Uses default `\Spryker\Shared\AiFoundation\AiFoundationConstants::AI_CONFIGURATION_DEFAULT` AI configuration if `PromptRequestTransfer.aiConfigurationName` is not provided.
     * - Throws exception if the specified AI configuration is not found.
     * - Validates that required configuration keys (`provider_name` and `provider_config`) are present and not empty.
     * - Resolves the AI provider instance based on the configuration.
     * - Applies system prompt from configuration if available.
     * - Maps the prompt message from `PromptRequestTransfer.promptMessage` to provider-specific format.
     * - Executes the chat request with the AI provider.
     * - Maps the provider's response to `PromptResponseTransfer`.
     * - Returns the AI provider's response.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @throws \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer;
}
