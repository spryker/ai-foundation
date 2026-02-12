<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

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
     *
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
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
     *
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;
}
