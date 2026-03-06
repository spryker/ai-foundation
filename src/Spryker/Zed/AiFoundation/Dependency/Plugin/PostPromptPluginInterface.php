<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Dependency\Plugin;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

/**
 * Provides an extension point to execute custom logic after an AI prompt lifecycle completes.
 */
interface PostPromptPluginInterface
{
    /**
     * Specification:
     * - Executes after AiFoundationFacade::prompt() completes successfully or with errors.
     * - Receives the original request and the completed response.
     * - MUST NOT mutate the response transfer.
     *
     * @api
     */
    public function postPrompt(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): void;
}
