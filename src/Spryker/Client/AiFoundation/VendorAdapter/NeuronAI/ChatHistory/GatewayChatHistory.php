<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\Messages\Message;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class GatewayChatHistory extends AbstractChatHistory
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_MESSAGE_NOT_DESERIALIZABLE = 'Stored chat history message at index %d of conversation "%s" was dropped because it could not be deserialized: %s';

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $bufferedMessages = [];

    /**
     * @param array<int, array<string, mixed>> $serializedMessages
     */
    public function __construct(
        protected AiFoundationStubInterface $aiFoundationStub,
        protected string $conversationReference,
        array $serializedMessages,
        int $contextWindow
    ) {
        parent::__construct($contextWindow);

        $this->history = $this->deserializeMessages($serializedMessages);
        $this->bufferedMessages = $this->serializeHistory();
    }

    public function flush(): ConversationHistoryCollectionResponseTransfer
    {
        $originalMessages = json_encode($this->bufferedMessages);

        $conversationHistoryCollectionRequestTransfer = (new ConversationHistoryCollectionRequestTransfer())
            ->addConversationHistory(
                (new ConversationHistoryTransfer())
                    ->setConversationReference($this->conversationReference)
                    ->setOriginalMessages($originalMessages === false ? '[]' : $originalMessages),
            );

        return $this->aiFoundationStub->saveConversationHistoryCollection($conversationHistoryCollectionRequestTransfer);
    }

    /**
     * @param array<\NeuronAI\Chat\Messages\Message> $messages
     */
    protected function setMessages(array $messages): void
    {
        $this->bufferedMessages = array_map(
            static fn ($message): array => $message->jsonSerialize(),
            $messages,
        );
    }

    protected function clear(): void
    {
        $this->bufferedMessages = [];
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     *
     * @return array<\NeuronAI\Chat\Messages\Message>
     */
    protected function deserializeMessages(array $messages): array
    {
        $deserializedMessages = [];

        foreach ($messages as $index => $message) {
            $deserializedMessage = $this->deserializeMessageOrNull($message, (int)$index);

            if ($deserializedMessage !== null) {
                $deserializedMessages[] = $deserializedMessage;
            }
        }

        return $deserializedMessages;
    }

    /**
     * @param array<string, mixed> $message
     */
    protected function deserializeMessageOrNull(array $message, int $index): ?Message
    {
        try {
            return parent::deserializeMessages([$message])[0] ?? null;
        } catch (Throwable $throwable) {
            $this->getLogger()->warning(
                sprintf(
                    static::LOG_MESSAGE_MESSAGE_NOT_DESERIALIZABLE,
                    $index,
                    $this->conversationReference,
                    $throwable->getMessage(),
                ),
                ['exception' => $throwable],
            );

            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function serializeHistory(): array
    {
        return array_map(
            static fn ($message): array => $message->jsonSerialize(),
            $this->history,
        );
    }
}
