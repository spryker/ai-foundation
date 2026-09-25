<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface PostPromptPluginInterface
{
    /**
     * Specification:
     * - Executes once per streamed turn, after the turn completes successfully or with errors.
     * - Does not execute when the chat session cannot be initialized; `streamPrompt()` then returns the session errors directly.
     * - Executes after the stream has been fully consumed and the chat history of the turn has been persisted, before `streamPrompt()` returns; a failed turn without streamed content persists no chat history.
     * - Executes also when the chat history is not persisted: a request without a conversation reference persists nothing, and a failure while persisting is logged.
     * - Executes before the caller reports the final `done` or `error` event, as `streamPrompt()` emits no event itself.
     * - Intended for side effects on the outcome of the turn, e.g. audit logging or usage metering.
     * - Receives the prompt request and the completed response of the turn.
     * - MUST NOT mutate the response transfer.
     * - A thrown exception is logged and does not stop the remaining plugins or fail the turn.
     *
     * @api
     */
    public function postPrompt(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): void;
}
