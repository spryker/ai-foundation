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
use Spryker\Client\ZedRequest\Stub\ZedRequestStub;

class AiFoundationStub extends ZedRequestStub implements AiFoundationStubInterface
{
    /**
     * @uses \Spryker\Zed\AiFoundation\Communication\Controller\GatewayController::promptAction
     */
    public function prompt(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\PromptResponseTransfer $promptResponseTransfer */
        $promptResponseTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/prompt',
            $promptRequestTransfer,
        );

        return $promptResponseTransfer;
    }

    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer {
        /** @var \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer $conversationHistoryCollectionTransfer */
        $conversationHistoryCollectionTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/get-conversation-history-collection',
            $conversationHistoryCriteriaTransfer,
        );

        return $conversationHistoryCollectionTransfer;
    }

    /**
     * @uses \Spryker\Zed\AiFoundation\Communication\Controller\GatewayController::initChatSessionAction
     */
    public function initChatSession(ChatSessionRequestTransfer $chatSessionRequestTransfer): ChatSessionResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\ChatSessionResponseTransfer $chatSessionResponseTransfer */
        $chatSessionResponseTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/init-chat-session',
            $chatSessionRequestTransfer,
        );

        return $chatSessionResponseTransfer;
    }

    /**
     * @uses \Spryker\Zed\AiFoundation\Communication\Controller\GatewayController::deleteConversationHistoryCollectionAction
     */
    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        /** @var \Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer $conversationHistoryCollectionResponseTransfer */
        $conversationHistoryCollectionResponseTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/delete-conversation-history-collection',
            $conversationHistoryCollectionDeleteCriteriaTransfer,
        );

        return $conversationHistoryCollectionResponseTransfer;
    }

    /**
     * @uses \Spryker\Zed\AiFoundation\Communication\Controller\GatewayController::saveConversationHistoryCollectionAction
     */
    public function saveConversationHistoryCollection(
        ConversationHistoryCollectionRequestTransfer $conversationHistoryCollectionRequestTransfer
    ): ConversationHistoryCollectionResponseTransfer {
        /** @var \Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer $conversationHistoryCollectionResponseTransfer */
        $conversationHistoryCollectionResponseTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/save-conversation-history-collection',
            $conversationHistoryCollectionRequestTransfer,
        );

        return $conversationHistoryCollectionResponseTransfer;
    }

    /**
     * @uses \Spryker\Zed\AiFoundation\Communication\Controller\GatewayController::createAiInteractionLogCollectionAction
     */
    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer {
        /** @var \Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer $aiInteractionLogCollectionResponseTransfer */
        $aiInteractionLogCollectionResponseTransfer = $this->zedStub->call(
            '/ai-foundation/gateway/create-ai-interaction-log-collection',
            $aiInteractionLogCollectionRequestTransfer,
        );

        return $aiInteractionLogCollectionResponseTransfer;
    }
}
