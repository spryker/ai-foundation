<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface VendorAdapterInterface
{
    /**
     * Sends a prompt request to the AI provider.
     *
     * If PromptRequestTransfer.structuredSchema is provided, the response will include
     * a structured message in PromptResponseTransfer.structuredMessage.
     *
     * Uses PromptRequestTransfer.maxRetries for retry attempts (defaults to 1).
     *
     * Returns PromptResponseTransfer with:
     * - isSuccessful: true if request succeeded, false if all retries failed
     * - errors: populated with error details if request failed
     * - message: regular response message (if no structured schema)
     * - structuredMessage: structured response (if structured schema provided)
     *
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer;

    /**
     * Retrieves conversation history collection based on criteria.
     *
     * Uses ConversationHistoryCriteriaTransfer.conversationHistoryConditions.conversationReferences to filter conversations.
     * Returns ConversationHistoryCollectionTransfer containing conversation histories.
     * Each conversation history includes conversation ID and all messages in the conversation.
     * Each message includes role (user, assistant, tool_call, tool_result), content, and attachments.
     * Returns empty collection if no conversations exist or all have expired.
     *
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;
}
