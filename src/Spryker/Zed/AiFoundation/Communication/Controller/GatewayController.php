<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Controller;

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
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 */
class GatewayController extends AbstractGatewayController
{
    public function promptAction(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer
    {
        $transferClassString = $promptRequestTransfer->getStructuredMessageClass();
        if ($transferClassString !== null) {
            $promptRequestTransfer->setStructuredMessage(new $transferClassString());
        }

        return $this->getFacade()->prompt($promptRequestTransfer);
    }

    public function getConversationHistoryCollectionAction(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer {
        return $this->getFacade()->getConversationHistoryCollection($conversationHistoryCriteriaTransfer);
    }

    public function initChatSessionAction(ChatSessionRequestTransfer $chatSessionRequestTransfer): ChatSessionResponseTransfer
    {
        return $this->getFacade()->initChatSession($chatSessionRequestTransfer);
    }

    public function deleteConversationHistoryCollectionAction(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        return $this->getFacade()->deleteConversationHistoryCollection($conversationHistoryCollectionDeleteCriteriaTransfer);
    }

    public function saveConversationHistoryCollectionAction(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        return $this->getFacade()->saveConversationHistoryCollection($conversationHistoryCollectionRequestTransfer);
    }

    public function createAiInteractionLogCollectionAction(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer {
        return $this->getFacade()->createAiInteractionLogCollection($aiInteractionLogCollectionRequestTransfer);
    }
}
