<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Creator;

use ArrayObject;
use Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;

class AiWorkflowItemCreator implements AiWorkflowItemCreatorInterface
{
    public function __construct(protected AiFoundationEntityManagerInterface $entityManager)
    {
    }

    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        $persistedItems = [];

        foreach ($aiWorkflowItemCollectionRequestTransfer->getAiWorkflowItems() as $index => $aiWorkflowItemTransfer) {
            $aiWorkflowItemTransfer->requireContextData();

            $persistedItems[$index] = $this->entityManager->createAiWorkflowItem($aiWorkflowItemTransfer);
        }

        return (new AiWorkflowItemCollectionResponseTransfer())
            ->setAiWorkflowItems(new ArrayObject($persistedItems))
            ->setIsSuccessful(true);
    }
}
