<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
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

    /**
     * Specification:
     * - Creates AI workflow items from collection request.
     * - Requires `AiWorkflowItemTransfer.processName` for each item.
     * - Requires `AiWorkflowItemTransfer.workflowContext` for each item.
     * - Validates that workflow item ID is not set for new items.
     * - Validates that process name is not empty.
     * - Validates that workflow context is not empty.
     * - Supports transactional operations via `AiWorkflowItemCollectionRequestTransfer.isTransactional` (default: true).
     * - On success: returns collection with all created items including generated IDs and state machine state.
     * - On failure: populates `AiWorkflowItemCollectionResponseTransfer.errors` with validation errors.
     * - Each error includes entity identifier to map error to specific item.
     * - Returns `AiWorkflowItemCollectionResponseTransfer` with created items and/or errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates AI workflow items from collection request.
     * - Requires `AiWorkflowItemTransfer.idAiWorkflowItem` for each item.
     * - Validates that workflow item ID is set and exists.
     * - Validates that process name is not empty if provided.
     * - Validates that workflow context is not empty if provided.
     * - Supports transactional operations via `AiWorkflowItemCollectionRequestTransfer.isTransactional` (default: true).
     * - On success: returns collection with all updated items.
     * - On failure: populates `AiWorkflowItemCollectionResponseTransfer.errors` with validation or update errors.
     * - Each error includes entity identifier to map error to specific item.
     * - Returns `AiWorkflowItemCollectionResponseTransfer` with updated items and/or errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function updateAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer;

    /**
     * Specification:
     * - Retrieves AI workflow items based on criteria.
     * - Filters by workflow item IDs via `AiWorkflowItemCriteriaTransfer.aiWorkflowItemConditions.aiWorkflowItemIds` (IN operation).
     * - Filters by state IDs via `AiWorkflowItemCriteriaTransfer.aiWorkflowItemConditions.stateIds` (IN operation).
     * - Filters by process names via `AiWorkflowItemCriteriaTransfer.aiWorkflowItemConditions.processNames` (IN operation).
     * - All conditions are combined with AND logic.
     * - Supports sorting via `AiWorkflowItemCriteriaTransfer.sortCollection`.
     * - Supports pagination via `AiWorkflowItemCriteriaTransfer.pagination`.
     * - Returns empty collection if no items match criteria.
     * - Returns collection of matching workflow items with all properties populated.
     * - Returns `AiWorkflowItemCollectionTransfer` with matching items and pagination info.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer;
}
