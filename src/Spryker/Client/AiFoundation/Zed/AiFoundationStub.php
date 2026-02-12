<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Zed;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\ZedRequest\Stub\ZedRequestStub;

class AiFoundationStub extends ZedRequestStub implements AiFoundationStubInterface
{
    /**
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequestTransfer
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
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

    /**
     * @param \Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
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
}
