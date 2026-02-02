<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use ArrayObject;
use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationRepository extends AbstractRepository implements AiFoundationRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function getAiWorkflowItemCollection(
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): AiWorkflowItemCollectionTransfer {
        $aiWorkflowItemCollection = new AiWorkflowItemCollectionTransfer();
        $aiWorkflowItemQuery = SpyAiWorkflowItemQuery::create();

        $aiWorkflowItemQuery = $this->applyAiWorkflowItemFilters(
            $aiWorkflowItemQuery,
            $aiWorkflowItemCriteriaTransfer,
        );

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem> $aiWorkflowItemEntities */
        $aiWorkflowItemEntities = $aiWorkflowItemQuery->find();

        if ($aiWorkflowItemEntities->count() === 0) {
            return $aiWorkflowItemCollection;
        }

        $aiWorkflowItemTransfers = $this->getFactory()
            ->createAiWorkflowItemMapper()
            ->mapAiWorkflowItemEntitiesToTransfers($aiWorkflowItemEntities->getArrayCopy());

        return $aiWorkflowItemCollection->setAiWorkflowItems(new ArrayObject($aiWorkflowItemTransfers));
    }

    /**
     * @param \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery $aiWorkflowItemQuery
     * @param \Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
     *
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery
     */
    protected function applyAiWorkflowItemFilters(
        SpyAiWorkflowItemQuery $aiWorkflowItemQuery,
        AiWorkflowItemCriteriaTransfer $aiWorkflowItemCriteriaTransfer
    ): SpyAiWorkflowItemQuery {
        $aiWorkflowItemConditions = $aiWorkflowItemCriteriaTransfer->getAiWorkflowItemConditions();

        if ($aiWorkflowItemConditions === null) {
            return $aiWorkflowItemQuery;
        }

        if ($aiWorkflowItemConditions->getAiWorkflowItemIds()) {
            $aiWorkflowItemQuery->filterByIdAiWorkflowItem_In(
                $aiWorkflowItemConditions->getAiWorkflowItemIds(),
            );
        }

        if ($aiWorkflowItemConditions->getStateIds()) {
            $aiWorkflowItemQuery->filterByFkStateMachineItemState_In(
                $aiWorkflowItemConditions->getStateIds(),
            );
        }

        return $aiWorkflowItemQuery;
    }
}
