<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\ContextBuilder;

use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

/**
 * Builds a populated AiInteractionLogTransfer from AI prompt request/response data.
 */
interface AiInteractionLogContextBuilderInterface
{
    public function buildPostPromptContext(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): AiInteractionLogTransfer;
}
