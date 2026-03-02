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
    public function __construct(protected AiFoundationRepositoryInterface $aiWorkflowItemRepository)
    {
    }

    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        return $this->aiWorkflowItemRepository->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);
    }
}
