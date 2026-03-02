<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater;

use ArrayObject;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;

class AiWorkflowItemUpdater implements AiWorkflowItemUpdaterInterface
{
    public function __construct(protected AiFoundationEntityManagerInterface $entityManager)
    {
    }

    public function updateAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        $persistedItems = [];

        foreach ($aiWorkflowItemCollectionRequestTransfer->getAiWorkflowItems() as $index => $aiWorkflowItemTransfer) {
            $aiWorkflowItemTransfer->requireIdAiWorkflowItem();

            $persistedItems[$index] = $this->entityManager->updateAiWorkflowItem($aiWorkflowItemTransfer);
        }

        return (new AiWorkflowItemCollectionResponseTransfer())
            ->setAiWorkflowItems(new ArrayObject($persistedItems))
            ->setIsSuccessful(true);
    }
}
