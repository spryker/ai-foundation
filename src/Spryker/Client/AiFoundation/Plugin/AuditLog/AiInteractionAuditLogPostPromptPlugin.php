<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Plugin\AuditLog;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationFactory getFactory()
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiInteractionAuditLogPostPromptPlugin extends AbstractPlugin implements PostPromptPluginInterface
{
    /**
     * {@inheritDoc}
     * - Records the prompt entry of the finished turn in the AI interaction log.
     * - Mirrors the entry onto the AI interaction audit log channel.
     * - Persists the entries collected during the turn to Zed in a single gateway call.
     *
     * @api
     */
    public function postPrompt(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): void {
        $aiInteractionLogCollector = $this->getFactory()->createAiInteractionLogCollector();

        $aiInteractionLogCollector->collectPrompt($promptRequestTransfer, $promptResponseTransfer);
        $aiInteractionLogCollector->flush();
    }
}
