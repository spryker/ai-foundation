<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface VendorAdapterInterface
{
    /**
     * Sends a prompt request to the AI provider.
     *
     * If PromptRequestTransfer.structuredSchema is provided, the response will include
     * a structured message in PromptResponseTransfer.structuredMessage.
     *
     * Uses PromptRequestTransfer.maxRetries for retry attempts (defaults to 1).
     *
     * Returns PromptResponseTransfer with:
     * - isSuccessful: true if request succeeded, false if all retries failed
     * - errors: populated with error details if request failed
     * - message: regular response message (if no structured schema)
     * - structuredMessage: structured response (if structured schema provided)
     *
     * @param \Generated\Shared\Transfer\PromptRequestTransfer $promptRequest
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function prompt(PromptRequestTransfer $promptRequest): PromptResponseTransfer;
}
