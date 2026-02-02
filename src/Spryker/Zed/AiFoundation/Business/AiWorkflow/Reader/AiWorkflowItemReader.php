<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Reader;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class AiWorkflowItemReader implements AiWorkflowItemReaderInterface
{
    /**
     * @param \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface $aiWorkflowItemRepository
     */
    public function __construct(protected AiFoundationRepositoryInterface $aiWorkflowItemRepository)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        return $this->aiWorkflowItemRepository->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);
    }
}
