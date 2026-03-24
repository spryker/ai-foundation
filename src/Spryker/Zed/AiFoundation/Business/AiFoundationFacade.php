<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiInteractionLogAggregationTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
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

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer
    ): AiInteractionLogCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiInteractionLogCreator()
            ->createAiInteractionLogCollection($aiInteractionLogCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemCreator()
            ->createAiWorkflowItemCollection($aiWorkflowItemCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemUpdater()
            ->updateAiWorkflowItemCollection($aiWorkflowItemCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemReader()
            ->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAiInteractionLogCollection(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiInteractionLogReader()
            ->getAiInteractionLogCollection($aiInteractionLogCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getAiInteractionLogAggregation(
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogAggregationTransfer {
        return $this->getFactory()
            ->createAiInteractionLogReader()
            ->getAiInteractionLogAggregation($aiInteractionLogCriteriaTransfer);
    }
}
