<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\AiToolCallTransfer;

interface PreToolCallPluginInterface
{
    /**
     * Specification:
     * - Executes before each individual AI tool call within a streamed prompt lifecycle.
     * - Receives AiToolCallTransfer containing tool name, arguments, and prompt request context.
     * - Receives AiToolCallTransfer.toolCallId as the identifier the AI provider generated for this call, or null when the provider returns none.
     * - Can set AiToolCallTransfer.isExecutionAllowed to false to prevent tool execution.
     * - Must return the (possibly modified) AiToolCallTransfer.
     * - A thrown exception fails the turn.
     *
     * @api
     */
    public function preToolCall(AiToolCallTransfer $aiToolCallTransfer): AiToolCallTransfer;
}
