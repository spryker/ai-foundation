<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\Plugin\AuditLog;

use Generated\Shared\Transfer\AiToolCallTransfer;
use Spryker\Client\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationFactory getFactory()
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiInteractionAuditLogPostToolCallPlugin extends AbstractPlugin implements PostToolCallPluginInterface
{
    /**
     * {@inheritDoc}
     * - Records the completed tool call in the AI interaction log of the turn.
     * - Mirrors the entry onto the AI interaction audit log channel.
     * - The entry reaches the gateway when the post-prompt flush of the same turn runs.
     *
     * @api
     */
    public function postToolCall(AiToolCallTransfer $aiToolCallTransfer): void
    {
        $this->getFactory()
            ->createAiInteractionLogCollector()
            ->collectToolCall($aiToolCallTransfer);
    }
}
