<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface AiFoundationFacadeInterface
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
     * - Gets requested tool sets from the stack of `\Spryker\Client\AiFoundation\Dependency\Tools\ToolSetPluginInterface` if `PromptRequestTransfer.toolSetNames` is provided.
     * - Extracts tools from the stacks and provides them to the AI provider.
     * - Maps the prompt message from `PromptRequestTransfer.promptMessage` to provider-specific format.
     * - If `PromptRequestTransfer.structuredMessage` is provided, executes the chat request with structured response format.
     * - Retries up to `PromptRequestTransfer.maxRetries` times if request fails (defaults to 1).
     * - Executes tool calls made by the AI provider during the conversation.
     * - Captures tool call information including tool name, arguments, and results.
     * - Populates `PromptResponseTransfer.toolInvocations` with all tool invocations executed during the conversation.
     * - Continues the conversation with tool results until the AI provides a final response.
     * - If `PromptRequestTransfer.conversationReference` is provided, retrieves storage-backed conversation history and appends it to the prompt.
     * - Saves or updates the conversation history back to storage after the prompt is processed.
     * - Maps the provider's structured response to `PromptResponseTransfer.structuredMessage` when schema is provided.
     * - The type of `PromptResponseTransfer.structuredMessage` will match the type of `PromptRequestTransfer.structuredMessage` provided in the request.
     * - Otherwise, executes a regular chat request and maps response to `PromptResponseTransfer.message`.
     * - Sets `PromptResponseTransfer.isSuccessful` to true if request succeeded, false if all retries failed.
     * - Populates `PromptResponseTransfer.errors` with error details if request failed.
     * - Returns the AI provider's response with success status and potential errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @throws \Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer;

    /**
     * Specification:
     * - Retrieves conversation history collection based on criteria.
     * - Uses `ConversationHistoryCriteriaTransfer.conversationHistoryConditions.conversationReferences` to filter conversations (IN operation).
     * - Returns `ConversationHistoryCollectionTransfer` containing conversation histories.
     * - Each conversation history includes conversation ID and all messages in the conversation.
     * - Each message includes type (user, assistant, tool_call, tool_result), content, and attachments.
     * - Returns empty collection if no conversations match criteria or all have expired.
     * - If `ConversationHistoryCriteriaTransfer.conversationHistoryConditions.conversationReferences` is empty, returns empty collection.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;
}
