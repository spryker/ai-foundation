<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\AiToolCallTransfer;

/**
 * Provides an extension point to execute custom logic before an AI tool is invoked.
 *
 * Use this plugin to implement permission checking, request validation, or event emission
 * before a tool executes. Setting AiToolCallTransfer.isExecutionAllowed to false will
 * skip tool execution.
 */
interface PreToolCallPluginInterface
{
    /**
     * Specification:
     * - Executes before each individual AI tool call within a prompt lifecycle.
     * - Receives AiToolCallTransfer containing tool name, arguments, and prompt request context.
     * - Can set AiToolCallTransfer.isExecutionAllowed to false to prevent tool execution.
     * - Must return the (possibly modified) AiToolCallTransfer.
     *
     * @api
     */
    public function preToolCall(AiToolCallTransfer $aiToolCallTransfer): AiToolCallTransfer;
}
