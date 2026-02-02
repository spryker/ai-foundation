<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\AiWorkflow\Deleter;

use Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class AiWorkflowItemDeleter implements AiWorkflowItemDeleterInterface
{
    use TransactionTrait;

    /**
     * @param \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface $repository
     * @param \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface $entityManager
     */
    public function __construct(
        protected AiFoundationRepositoryInterface $repository,
        protected AiFoundationEntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    public function deleteAiWorkflowItemCollection(
        AiWorkflowItemCollectionDeleteCriteriaTransfer $aiWorkflowItemCollectionDeleteCriteriaTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        $criteria = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions(
                (new AiWorkflowItemConditionsTransfer())
                    ->setAiWorkflowItemIds($aiWorkflowItemCollectionDeleteCriteriaTransfer->getAiWorkflowItemIds()),
            );

        $collection = $this->repository->getAiWorkflowItemCollection($criteria);

        return $this->getTransactionHandler()->handleTransaction(
            function () use ($collection) {
                return $this->executeDeleteTransaction($collection);
            },
        );
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionResponseTransfer
     */
    protected function executeDeleteTransaction(
        AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
    ): AiWorkflowItemCollectionResponseTransfer {
        foreach ($aiWorkflowItemCollectionTransfer->getAiWorkflowItems() as $aiWorkflowItemTransfer) {
            $this->entityManager->deleteAiWorkflowItem($aiWorkflowItemTransfer->getIdAiWorkflowItemOrFail());
        }

        return (new AiWorkflowItemCollectionResponseTransfer())
            ->setAiWorkflowItems($aiWorkflowItemCollectionTransfer->getAiWorkflowItems())
            ->setIsSuccessful(true);
    }
}
