<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use JsonException;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
use Propel\Runtime\Collection\ObjectCollection;

class AiWorkflowItemMapper implements AiWorkflowItemMapperInterface
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
    ): AiWorkflowItemTransfer {
        $aiWorkflowItemTransfer->fromArray($aiWorkflowItemEntity->toArray(), true);

        $contextDataJson = $aiWorkflowItemEntity->getContextData();
        if ($contextDataJson) {
            try {
                $contextData = json_decode($contextDataJson, true, 512, JSON_THROW_ON_ERROR);
                $aiWorkflowItemTransfer->setContextData($contextData);
            } catch (JsonException $jsonException) {
                $aiWorkflowItemTransfer->setContextData([]);
            }
        }

        return $aiWorkflowItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     * @param \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem $aiWorkflowItemEntity
     *
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem
     */
    public function mapAiWorkflowItemTransferToEntity(
        AiWorkflowItemTransfer $aiWorkflowItemTransfer,
        SpyAiWorkflowItem $aiWorkflowItemEntity
    ): SpyAiWorkflowItem {
        $transferData = $aiWorkflowItemTransfer->modifiedToArray();
        unset($transferData['context_data']);

        $aiWorkflowItemEntity->fromArray($transferData);

        $contextData = $aiWorkflowItemTransfer->getContextData();
        $contextDataJson = json_encode($contextData, JSON_THROW_ON_ERROR);
        $aiWorkflowItemEntity->setContextData($contextDataJson);

        return $aiWorkflowItemEntity;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem> $aiWorkflowItemEntities
     *
     * @return array<\Generated\Shared\Transfer\AiWorkflowItemTransfer>
     */
    public function mapAiWorkflowItemEntitiesToTransfers(ObjectCollection $aiWorkflowItemEntities): array
    {
        $aiWorkflowItemTransfers = [];

        foreach ($aiWorkflowItemEntities as $aiWorkflowItemEntity) {
            $aiWorkflowItemTransfers[] = $this->mapAiWorkflowItemEntityToTransfer(
                $aiWorkflowItemEntity,
                new AiWorkflowItemTransfer(),
            );
        }

        return $aiWorkflowItemTransfers;
    }
}
