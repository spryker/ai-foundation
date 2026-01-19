<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
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
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        return $this->getFactory()
            ->createAiWorkflowItemWriter()
            ->createAiWorkflowItem($aiWorkflowItemTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemState(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        return $this->getFactory()
            ->createAiWorkflowItemWriter()
            ->updateAiWorkflowItemState($aiWorkflowItemTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItemContext(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        return $this->getFactory()
            ->createAiWorkflowItemWriter()
            ->updateAiWorkflowItemContext($aiWorkflowItemTransfer);
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
