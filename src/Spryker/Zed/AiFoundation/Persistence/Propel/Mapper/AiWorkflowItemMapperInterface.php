<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
use Propel\Runtime\Collection\ObjectCollection;

interface AiWorkflowItemMapperInterface
{
    /**
     * @param \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem $aiWorkflowItemEntity
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function mapAiWorkflowItemEntityToTransfer(
        SpyAiWorkflowItem $aiWorkflowItemEntity,
        AiWorkflowItemTransfer $aiWorkflowItemTransfer
    ): AiWorkflowItemTransfer;

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     * @param \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem $aiWorkflowItemEntity
     *
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem
     */
    public function mapAiWorkflowItemTransferToEntity(
        AiWorkflowItemTransfer $aiWorkflowItemTransfer,
        SpyAiWorkflowItem $aiWorkflowItemEntity
    ): SpyAiWorkflowItem;

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem> $aiWorkflowItemEntities
     *
     * @return array<\Generated\Shared\Transfer\AiWorkflowItemTransfer>
     */
    public function mapAiWorkflowItemEntitiesToTransfers(ObjectCollection $aiWorkflowItemEntities): array;
}
