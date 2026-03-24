<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Controller;

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
}
