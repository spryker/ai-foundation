<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
use Propel\Runtime\Collection\ObjectCollection;

interface AiWorkflowItemMapperInterface
{
    public function mapAiWorkflowItemEntityToTransfer(
        SpyAiWorkflowItem $aiWorkflowItemEntity,
        AiWorkflowItemTransfer $aiWorkflowItemTransfer
    ): AiWorkflowItemTransfer;

    public function mapAiWorkflowItemTransferToEntity(
        AiWorkflowItemTransfer $aiWorkflowItemTransfer,
        SpyAiWorkflowItem $aiWorkflowItemEntity
    ): SpyAiWorkflowItem;

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem> $aiWorkflowItemEntities
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function mapAiWorkflowItemEntityCollectionToAiWorkflowItemCollectionTransfer(
        ObjectCollection $aiWorkflowItemEntities,
        AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
    ): AiWorkflowItemCollectionTransfer;
}
