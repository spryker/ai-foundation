<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation;

use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class AiFoundationConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Defines the default maximum context window size for conversation history.
     * - Value is expressed in tokens.
     * - Represents the maximum number of tokens to retain in conversation history storage per conversation.
     * - When the conversation history exceeds this limit, the oldest messages are automatically pruned to stay within the limit.
     * - This limit is applied per conversation, not globally across all conversations.
     * - Helps prevent storage overflow and ensures conversation history fits within AI model context limits.
     *
     * @var int
     */
    protected const DEFAULT_CONVERSATION_HISTORY_CONTEXT_WINDOW = 50000;

    /**
     * @api
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAiConfigurations(): array
    {
        return $this->get(AiFoundationConstants::AI_CONFIGURATIONS);
    }

    /**
     * Specification:
     * - Returns the maximum context window size for conversation history.
     * - Value is expressed in tokens.
     * - Represents the maximum number of tokens to retain in conversation history storage per conversation.
     * - When the conversation history exceeds this limit, the oldest messages are automatically pruned to stay within the limit.
     * - This limit is applied per conversation, not globally across all conversations.
     * - Helps prevent storage overflow and ensures conversation history fits within AI model context limits.
     *
     * @api
     *
     * @return int
     */
    public function getConversationHistoryContextWindow(): int
    {
        return $this->get(AiFoundationConstants::CONVERSATION_HISTORY_CONTEXT_WINDOW, static::DEFAULT_CONVERSATION_HISTORY_CONTEXT_WINDOW);
    }
}
