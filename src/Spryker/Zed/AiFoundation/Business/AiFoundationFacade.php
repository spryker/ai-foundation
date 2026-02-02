<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getFactory()
 */
class AiFoundationFacade extends AbstractFacade implements AiFoundationFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
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
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function updateAiWorkflowItemStateCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemStateUpdater()
            ->updateAiWorkflowItemStateCollection($aiWorkflowItemCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function updateAiWorkflowItemContextCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemContextUpdater()
            ->updateAiWorkflowItemContextCollection($aiWorkflowItemCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function deleteAiWorkflowItemCollection(
        AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemDeleter()
            ->deleteAiWorkflowItemCollection($aiWorkflowItemCollectionDeleteCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        return $this->getFactory()
            ->createAiWorkflowItemReader()
            ->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);
    }
}
