<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Prompt;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface PromptSenderInterface
{
    /**
     * Sends a prompt to Zed, handling structured message serialization and deserialization.
     *
     * @api
     */
    public function sendPrompt(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer;
}
