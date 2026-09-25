<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;

class GatewayChatHistoryFactory implements GatewayChatHistoryFactoryInterface
{
    protected const int DEFAULT_CONTEXT_WINDOW = 50000;

    public function __construct(
        protected AiFoundationStubInterface $aiFoundationStub
    ) {
    }

    public function createGatewayChatHistory(
        ChatSessionRequestTransfer $chatSessionRequestTransfer,
        ChatSessionResponseTransfer $chatSessionResponseTransfer
    ): GatewayChatHistory {
        return new GatewayChatHistory(
            $this->aiFoundationStub,
            (string)$chatSessionRequestTransfer->getConversationReference(),
            $this->extractSerializedMessages($chatSessionResponseTransfer),
            $chatSessionResponseTransfer->getContextWindow() ?? static::DEFAULT_CONTEXT_WINDOW,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function extractSerializedMessages(ChatSessionResponseTransfer $chatSessionResponseTransfer): array
    {
        $originalMessages = $chatSessionResponseTransfer->getConversationHistory()?->getOriginalMessages();

        if ($originalMessages === null || $originalMessages === '') {
            return [];
        }

        $decodedMessages = json_decode($originalMessages, true);

        if (!is_array($decodedMessages)) {
            return [];
        }

        return array_values(array_filter($decodedMessages, 'is_array'));
    }
}
