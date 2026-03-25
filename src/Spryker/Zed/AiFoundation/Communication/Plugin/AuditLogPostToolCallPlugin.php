<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Plugin;

use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\AuditLoggerConfigCriteriaTransfer;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\Log\AuditLoggerTrait;
use Spryker\Zed\AiFoundation\Dependency\Plugin\PostToolCallPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * Logs AI tool call interactions to the audit log channel after each tool call completes.
 *
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getBusinessFactory()
 */
class AuditLogPostToolCallPlugin extends AbstractPlugin implements PostToolCallPluginInterface
{
    use AuditLoggerTrait;

    protected const string LOG_MESSAGE_AI_INTERACTION = 'AI interaction';

    /**
     * {@inheritDoc}
     * - Logs the AI tool call interaction to \Spryker\Shared\AiFoundation\AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION audit log channel.
     *
     * @api
     */
    public function postToolCall(AiToolCallTransfer $aiToolCallTransfer): void
    {
        $logger = $this->getAuditLogger(
            (new AuditLoggerConfigCriteriaTransfer())
                ->setChannelName(AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION),
        );

        $aiInteractionLogTransfer = $this->getBusinessFactory()
            ->createAiInteractionLogContextBuilder()
            ->buildPostToolCallContext($aiToolCallTransfer);

        $logger->info(
            static::LOG_MESSAGE_AI_INTERACTION,
            [AiFoundationConstants::AUDIT_LOG_CONTEXT_KEY_TRANSFER => $aiInteractionLogTransfer],
        );
    }
}
