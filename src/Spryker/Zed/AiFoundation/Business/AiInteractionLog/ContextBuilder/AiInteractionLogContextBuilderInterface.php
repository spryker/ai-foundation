<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\ContextBuilder;

use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

/**
 * Builds a populated AiInteractionLogTransfer from AI interaction data for audit logging.
 */
interface AiInteractionLogContextBuilderInterface
{
    /**
     * Builds an AiInteractionLogTransfer from prompt request and response data for post-prompt audit logging.
     * Includes metadata such as context identifier, errors, and structured schema class when applicable.
     */
    public function buildPostPromptContext(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): AiInteractionLogTransfer;

    /**
     * Builds an AiInteractionLogTransfer from tool call data for post-tool-call audit logging.
     * Includes tool name, arguments, result, and context identifier in metadata.
     */
    public function buildPostToolCallContext(AiToolCallTransfer $aiToolCallTransfer): AiInteractionLogTransfer;
}
