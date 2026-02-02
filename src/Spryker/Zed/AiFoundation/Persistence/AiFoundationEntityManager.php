<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getFactory()
 */
class AiFoundationEntityManager extends AbstractEntityManager implements AiFoundationEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function createAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemEntity = new SpyAiWorkflowItem();
        $aiWorkflowItemEntity = $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemTransferToEntity(
            $aiWorkflowItemTransfer,
            $aiWorkflowItemEntity,
        );

        $aiWorkflowItemEntity->save();

        return $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemEntityToTransfer(
            $aiWorkflowItemEntity,
            $aiWorkflowItemTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function updateAiWorkflowItem(AiWorkflowItemTransfer $aiWorkflowItemTransfer): AiWorkflowItemTransfer
    {
        $aiWorkflowItemTransfer->requireIdAiWorkflowItem();

        $aiWorkflowItemEntity = SpyAiWorkflowItemQuery::create()
            ->filterByIdAiWorkflowItem($aiWorkflowItemTransfer->getIdAiWorkflowItem())
            ->findOne();

        if ($aiWorkflowItemEntity === null) {
            return $aiWorkflowItemTransfer;
        }

        $aiWorkflowItemEntity = $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemTransferToEntity(
            $aiWorkflowItemTransfer,
            $aiWorkflowItemEntity,
        );

        $aiWorkflowItemEntity->save();

        return $this->getFactory()->createAiWorkflowItemMapper()->mapAiWorkflowItemEntityToTransfer(
            $aiWorkflowItemEntity,
            $aiWorkflowItemTransfer,
        );
    }

    /**
     * @param int $idAiWorkflowItem
     *
     * @return void
     */
    public function deleteAiWorkflowItem(int $idAiWorkflowItem): void
    {
        SpyAiWorkflowItemQuery::create()
            ->filterByIdAiWorkflowItem($idAiWorkflowItem)
            ->delete();
    }
}
