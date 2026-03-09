<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Plugin;

use Generated\Shared\Transfer\AuditLoggerConfigCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\Log\AuditLoggerTrait;
use Spryker\Zed\AiFoundation\Dependency\Plugin\PostPromptPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * Logs AI prompt interactions to the audit log channel after each prompt lifecycle completes.
 *
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationBusinessFactory getBusinessFactory()
 */
class AuditLogPostPromptPlugin extends AbstractPlugin implements PostPromptPluginInterface
{
    use AuditLoggerTrait;

    protected const string LOG_MESSAGE_AI_INTERACTION = 'AI interaction';

    /**
     * {@inheritDoc}
     * - Logs the AI prompt interaction to \Spryker\Shared\AiFoundation\AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION audit log channel.
     *
     * @api
     */
    public function postPrompt(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): void {
        $logger = $this->getAuditLogger(
            (new AuditLoggerConfigCriteriaTransfer())
                ->setChannelName(AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION),
        );

        $aiInteractionLogTransfer = $this->getBusinessFactory()
            ->createAiInteractionLogContextBuilder()
            ->buildPostPromptContext($promptRequestTransfer, $promptResponseTransfer);

        $logger->info(
            static::LOG_MESSAGE_AI_INTERACTION,
            [AiFoundationConstants::AUDIT_LOG_CONTEXT_KEY_TRANSFER => $aiInteractionLogTransfer],
        );
    }
}
