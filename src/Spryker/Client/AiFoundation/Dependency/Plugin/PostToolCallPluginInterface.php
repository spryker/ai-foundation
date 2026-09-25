<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\AiToolCallTransfer;

interface PostToolCallPluginInterface
{
    /**
     * Specification:
     * - Executes once per tool call, right after the tool ran or was skipped by a pre-tool-call plugin, and before its result is sent back to the model, i.e. mid-turn before the final answer is streamed.
     * - Intended for side effects on a single tool call, e.g. audit logging.
     * - Receives AiToolCallTransfer containing tool name, arguments, result, and prompt request context.
     * - Receives AiToolCallTransfer.toolCallId as the identifier the AI provider generated for this call, or null when the provider returns none.
     * - Receives AiToolCallTransfer.isExecutionAllowed as false when a pre-tool-call plugin skipped the execution.
     * - Must not mutate the tool result.
     * - A thrown exception fails the turn.
     *
     * @api
     */
    public function postToolCall(AiToolCallTransfer $aiToolCallTransfer): void;
}
