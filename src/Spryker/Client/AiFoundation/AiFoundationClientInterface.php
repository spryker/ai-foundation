<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface AiFoundationClientInterface
{
    /**
     * Specification:
     * - Makes call to Zed.
     * - Sends prompt request to configured AI provider.
     * - Resolves AI configuration by name from `PromptRequestTransfer.aiConfigurationName`.
     * - Executes tool calls if tools are provided.
     * - Maintains conversation history if `PromptRequestTransfer.conversationReference` is provided.
     * - Retries request up to `PromptRequestTransfer.maxRetries` times on failure.
     * - Returns structured response if schema is provided in `PromptRequestTransfer.structuredMessage`.
     * - Returns response with success status and errors.
     *
     * @api
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer;

    /**
     * Specification:
     * - Makes call to Zed.
     * - Retrieves conversation history collection based on criteria.
     * - Filters conversations by `ConversationHistoryCriteriaTransfer.conversationHistoryConditions.conversationReferences`.
     * - Returns conversation histories with all messages.
     * - Returns empty collection if no conversations match criteria.
     *
     * @api
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;

    /**
     * Specification:
     * - Streams one prompt turn and owns the full lifecycle of it; the caller supplies only the request.
     * - Resolves the AI configuration named by `PromptRequestTransfer.aiConfigurationName` from the client-resolvable allowlist.
     * - Overrides the resolved system prompt with `PromptRequestTransfer.systemPrompt` when it is not empty.
     * - Loads the chat history of `PromptRequestTransfer.conversationReference` and appends the request prompt message to it.
     * - Calls the AI provider directly from the Client layer; makes no Zed call for the provider itself.
     * - Before streaming, provides the model with the `\Spryker\Client\AiFoundation\Dependency\Tools\ToolPluginInterface` plugins whose names match `PromptRequestTransfer.toolNames`.
     * - Ignores `PromptRequestTransfer.toolSetNames`.
     * - Returns an unsuccessful response without calling the AI provider when any of `PromptRequestTransfer.toolNames` matches no registered tool plugin.
     * - Runs a bounded tool-call loop: executes requested tools, feeds their results back and re-streams until the model answers or the iteration cap is reached.
     * - Reports one stream chunk per mappable vendor chunk to the registered `\Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface` plugins, together with the request of the turn.
     * - Offers no per-call stream sink: every registered stream event plugin receives the chunks of every turn and filters them by the request.
     * - Emits no event itself; the registered plugins and the caller report those.
     * - Returns an unsuccessful response with errors when the session cannot be initialized or the stream fails.
     * - Returns the content accumulated from the streamed tokens in `PromptResponseTransfer.streamedContent`.
     * - Persists the chat history of the turn, keeping a partial reply as a truncated assistant message; a turn that produced no content at all is not persisted.
     * - Skips persisting the chat history when `PromptRequestTransfer.conversationReference` is not provided.
     * - Logs a failed or rejected chat history save without failing the turn.
     * - Persists the AI interaction log of the turn; a failure to audit never fails a reply that was already produced.
     * - Executes the registered `\Spryker\Client\AiFoundation\Dependency\Plugin\PreToolCallPluginInterface` plugins before each tool call.
     * - Executes the registered `\Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface` plugins after each tool call.
     * - Executes the registered `\Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface` plugins after the turn completes.
     *
     * @api
     */
    public function streamPrompt(
        PromptRequestTransfer $promptRequestTransfer
    ): PromptResponseTransfer;

    /**
     * Specification:
     * - Makes one call to Zed.
     * - Removes the persisted chat history of `ConversationHistoryCollectionDeleteCriteriaTransfer.conversationHistoryConditions.conversationReferences`.
     * - Returns an unsuccessful response when no usable conversation reference is given, without deleting anything.
     *
     * @api
     */
    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer;
}
