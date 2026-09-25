<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Generated\Shared\Transfer\AiPriceMapTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
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
     * @throws \Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Exception\NeuronAiConfigurationException
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
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;

    /**
     * Specification:
     * - Returns the resolved AI configuration and the stored chat history in one response.
     * - Returns an unsuccessful response when `ChatSessionRequestTransfer.aiConfigurationName` is not present in the client resolvable allowlist, so a caller cannot enumerate configurations.
     * - Returns an unsuccessful response when the resolved AI configuration is missing `provider_name` or `provider_config`.
     * - Expands configuration-management references into live values before returning.
     * - Returns the chat history of `ChatSessionRequestTransfer.conversationReference`; the caller owns scoping the conversation to its actor.
     * - Returns no chat history when `ChatSessionRequestTransfer.conversationReference` is not provided.
     * - Populates `ChatSessionResponseTransfer.contextWindow` with the configured conversation history context window.
     * - Populates `ChatSessionResponseTransfer.errors` with error details when unsuccessful.
     *
     * @api
     */
    public function initChatSession(ChatSessionRequestTransfer $chatSessionRequestTransfer): ChatSessionResponseTransfer;

    /**
     * Specification:
     * - Persists `ConversationHistoryTransfer.originalMessages` of every `ConversationHistoryCollectionRequestTransfer.conversationHistories` item as the chat history of its `ConversationHistoryTransfer.conversationReference`.
     * - Creates the chat history when none exists for the conversation reference, otherwise overwrites it; the chat history is not owner scoped, so the caller owns scoping the conversation to its actor before calling.
     * - Persists all items in one transaction.
     * - Returns an unsuccessful response without persisting anything when no item is given or any item has no `ConversationHistoryTransfer.conversationReference`.
     * - Returns the persisted chat histories in `ConversationHistoryCollectionResponseTransfer.conversationHistories`.
     * - Populates `ConversationHistoryCollectionResponseTransfer.errors` with error details when unsuccessful.
     *
     * @api
     */
    public function saveConversationHistoryCollection(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): ConversationHistoryCollectionResponseTransfer;

    /**
     * Specification:
     * - Removes the persisted chat history of `ConversationHistoryCollectionDeleteCriteriaTransfer.conversationHistoryConditions.conversationReferences` (IN operation).
     * - Returns an unsuccessful response without deleting anything when no usable reference is given.
     * - Populates `ConversationHistoryCollectionResponseTransfer.errors` with error details when unsuccessful.
     *
     * @api
     */
    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer;

    /**
     * Specification:
     * - Creates AI interaction log entries from a collection request.
     * - Persists log records for auditing AI prompt interactions.
     * - Returns `AiInteractionLogCollectionResponseTransfer` with created entries.
     *
     * @api
     */
    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer;

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
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer;

    /**
     * Specification:
     * - Retrieves AI interaction log collection filtered by `AiInteractionLogCriteriaTransfer.aiInteractionLogConditions`.
     * - Returns an empty collection if no logs match criteria or any criteria is not set.
     *
     * @api
     */
    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionResponseTransfer;

    /**
     * Specification:
     * - Returns aggregated statistics for AI interaction logs filtered by criteria.
     * - Calculates total request count, total tokens consumed, success rate percentage, and average inference time.
     * - Calculates the grand total estimated USD cost and a per-provider/model cost breakdown using the configured token prices; interactions with no configured price are excluded from the total and reported as unpriced combinations.
     * - Applies the same filtering conditions as `getAiInteractionLogCollection()`.
     * - Returns zero-value aggregation if no logs match criteria.
     *
     * @api
     */
    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer;

    /**
     * Specification:
     * - Returns the configured token price (USD per 1,000,000 tokens) for every priceable provider/model.
     * - Each entry is marked priced only when both its input and output prices are configured as non-negative decimals; otherwise it is unpriced.
     * - Reads prices from configuration management at the global scope; returns unpriced entries when no prices are configured.
     *
     * @api
     */
    public function getAiPriceMap(): AiPriceMapTransfer;
}
