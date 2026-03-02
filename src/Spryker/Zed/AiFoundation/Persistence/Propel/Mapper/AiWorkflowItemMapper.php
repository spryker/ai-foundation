<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use JsonException;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItem;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Mapper for transforming AI workflow item data between entity and transfer objects.
 */
class AiWorkflowItemMapper implements AiWorkflowItemMapperInterface
{
    /**
     * @var int<1, max>
     */
    protected const int DEFAULT_CONTEXT_DATA_JSON_DECODE_DEPTH = 512;

    public function mapAiWorkflowItemEntityToTransfer(
        SpyAiWorkflowItem $aiWorkflowItemEntity,
        AiWorkflowItemTransfer $aiWorkflowItemTransfer
    ): AiWorkflowItemTransfer {
        $aiWorkflowItemTransfer->fromArray($aiWorkflowItemEntity->toArray(), true);

        $contextDataJson = $aiWorkflowItemEntity->getContextData();

        if ($contextDataJson) {
            try {
                $contextData = json_decode(
                    $contextDataJson,
                    true,
                    static::DEFAULT_CONTEXT_DATA_JSON_DECODE_DEPTH,
                    JSON_THROW_ON_ERROR,
                );
                $aiWorkflowItemTransfer->setContextData($contextData);
            } catch (JsonException $jsonException) {
                $aiWorkflowItemTransfer->setContextData([]);
            }
        }

        return $aiWorkflowItemTransfer;
    }

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
     * @param \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemCollectionTransfer
     */
    public function mapAiWorkflowItemEntityCollectionToAiWorkflowItemCollectionTransfer(
        ObjectCollection $aiWorkflowItemEntities,
        AiWorkflowItemCollectionTransfer $aiWorkflowItemCollectionTransfer
    ): AiWorkflowItemCollectionTransfer {
        foreach ($aiWorkflowItemEntities as $aiWorkflowItemEntity) {
            $aiWorkflowItemTransfer = $this->mapAiWorkflowItemEntityToTransfer(
                $aiWorkflowItemEntity,
                new AiWorkflowItemTransfer(),
            );

            $aiWorkflowItemCollectionTransfer->addAiWorkflowItem($aiWorkflowItemTransfer);
        }

        return $aiWorkflowItemCollectionTransfer;
    }
}
