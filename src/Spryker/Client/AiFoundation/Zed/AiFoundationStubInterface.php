<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Zed;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface AiFoundationStubInterface
{
    public function prompt(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer;

    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;

    public function initChatSession(ChatSessionRequestTransfer $chatSessionRequestTransfer): ChatSessionResponseTransfer;

    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer;

    public function saveConversationHistoryCollection(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): ConversationHistoryCollectionResponseTransfer;

    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer;
}
