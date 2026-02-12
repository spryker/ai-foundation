<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationFacade extends AbstractFacade implements AiFoundationFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer
    {
        return $this->getFactory()
            ->getVendorAdapterPlugin()
            ->getVendorAdapter()
            ->prompt($promptRequest);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer {
        return $this->getFactory()
            ->getVendorAdapterPlugin()
            ->getVendorAdapter()
            ->getConversationHistoryCollection($conversationHistoryCriteriaTransfer);
    }
}
