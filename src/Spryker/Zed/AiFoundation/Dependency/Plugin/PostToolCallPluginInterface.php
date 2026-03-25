<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\AiToolCallTransfer;

/**
 * Provides an extension point to execute custom logic after an AI tool has been invoked.
 *
 * Use this plugin to implement audit logging, event emission, or response enrichment
 * after a tool completes execution.
 */
interface PostToolCallPluginInterface
{
    /**
     * Specification:
     * - Executes after each individual AI tool call within a prompt lifecycle.
     * - Receives AiToolCallTransfer containing tool name, arguments, result, and prompt request context.
     * - Must not mutate the tool result.
     *
     * @api
     */
    public function postToolCall(AiToolCallTransfer $aiToolCallTransfer): void;
}
