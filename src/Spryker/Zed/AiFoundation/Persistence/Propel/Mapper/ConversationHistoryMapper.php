<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence\Propel\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistory;

class ConversationHistoryMapper
{
    public function mapEntityToTransfer(
        SpyAiConversationHistory $entity,
        ConversationHistoryTransfer $transfer
    ): ConversationHistoryTransfer {
        $transfer->setConversationReference($entity->getConversationReference());
        $transfer->setOriginalMessages($entity->getOriginalMessages());
        $transfer->setCreatedAt($entity->getCreatedAt()?->format('Y-m-d H:i:s'));
        $transfer->setUpdatedAt($entity->getUpdatedAt()?->format('Y-m-d H:i:s'));

        $messages = $this->deserializeMessages($entity->getOriginalMessages());
        $transfer->setMessages(new ArrayObject($messages));

        return $transfer;
    }

    public function mapTransferToEntity(
        ConversationHistoryTransfer $transfer,
        SpyAiConversationHistory $entity
    ): SpyAiConversationHistory {
        $entity->setConversationReference($transfer->getConversationReferenceOrFail());
        $entity->setOriginalMessages($transfer->getOriginalMessagesOrFail());

        return $entity;
    }

    /**
     * @param array<\Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistory> $conversationHistoryEntities
     * @param \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer $conversationHistoryCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\ConversationHistoryCollectionTransfer
     */
    public function mapConversationHistoryEntityCollectionToConversationHistoryCollectionTransfer(
        array $conversationHistoryEntities,
        ConversationHistoryCollectionTransfer $conversationHistoryCollectionTransfer
    ): ConversationHistoryCollectionTransfer {
        foreach ($conversationHistoryEntities as $conversationHistoryEntity) {
            $conversationHistoryCollectionTransfer->addConversationHistory(
                $this->mapEntityToTransfer(
                    $conversationHistoryEntity,
                    new ConversationHistoryTransfer(),
                ),
            );
        }

        return $conversationHistoryCollectionTransfer;
    }

    /**
     * @param string $messagesJson
     *
     * @return array<\Generated\Shared\Transfer\PromptMessageTransfer>
     */
    protected function deserializeMessages(string $messagesJson): array
    {
        $messagesData = json_decode($messagesJson, true);

        if ($messagesData === null) {
            return [];
        }

        $messages = [];

        foreach ($messagesData as $messageData) {
            $messages[] = (new PromptMessageTransfer())->fromArray($messageData, true);
        }

        return $messages;
    }
}
