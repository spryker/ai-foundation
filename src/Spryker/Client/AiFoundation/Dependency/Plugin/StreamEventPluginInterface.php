<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptStreamChunkTransfer;

interface StreamEventPluginInterface
{
    /**
     * Specification:
     * - Executes for each mappable vendor chunk of a streamed prompt lifecycle.
     * - Receives the prompt request of the turn so the plugin can ignore turns it does not own.
     * - Must not throw; a failure to report one event must not fail the turn.
     *
     * @api
     */
    public function onStreamEvent(
        PromptStreamChunkTransfer $promptStreamChunkTransfer,
        PromptRequestTransfer $promptRequestTransfer
    ): void;
}
