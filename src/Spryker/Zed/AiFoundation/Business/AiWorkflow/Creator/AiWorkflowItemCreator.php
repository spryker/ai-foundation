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
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class AiWorkflowItemCreator implements AiWorkflowItemCreatorInterface
{
    use TransactionTrait;

    /**
     * @param \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface $entityManager
     */
    public function __construct(protected AiFoundationEntityManagerInterface $entityManager)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function createAiWorkflowItemCollection(
        AiWorkflowItemCollectionRequestTransfer $aiWorkflowItemCollectionRequestTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        $items = [];

        foreach ($aiWorkflowItemCollectionRequestTransfer->getAiWorkflowItems() as $index => $aiWorkflowItemTransfer) {
            $aiWorkflowItemTransfer->requireContextData();

            $items[$index] = $aiWorkflowItemTransfer;
        }

        $persistedItems = $this->getTransactionHandler()->handleTransaction(
            function () use ($items) {
                return $this->executeCreateTransaction($items);
            },
        );

        return (new AiWorkflowItemCollectionResponseTransfer())
            ->setAiWorkflowItems(new ArrayObject($persistedItems))
            ->setIsSuccessful(true);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AiWorkflowItemTransfer> $aiWorkflowItemTransfers
     *
     * @return array<\Generated\Shared\Transfer\AiWorkflowItemTransfer>
     */
    protected function executeCreateTransaction(array $aiWorkflowItemTransfers): array
    {
        $persistedTransfers = [];

        foreach ($aiWorkflowItemTransfers as $key => $aiWorkflowItemTransfer) {
            $persistedTransfers[$key] = $this->entityManager->createAiWorkflowItem($aiWorkflowItemTransfer);
        }

        return $persistedTransfers;
    }
}
