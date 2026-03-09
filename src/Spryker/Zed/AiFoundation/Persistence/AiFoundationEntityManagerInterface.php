<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;

interface AiFoundationEntityManagerInterface
{
    public function saveConversationHistory(
        ConversationHistoryTransfer $conversationHistoryTransfer
    ): ConversationHistoryTransfer;

    public function deleteConversationHistory(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): void;

    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    public function updateAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer;

    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer;
}
