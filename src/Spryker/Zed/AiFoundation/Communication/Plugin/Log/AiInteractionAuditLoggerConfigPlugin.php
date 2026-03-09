<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Plugin\Log;

use Generated\Shared\Transfer\AuditLoggerConfigCriteriaTransfer;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\LogExtension\Dependency\Plugin\AuditLoggerConfigPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * Provides audit logger configuration for the AI interaction log channel.
 *
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiInteractionAuditLoggerConfigPlugin extends AbstractPlugin implements AuditLoggerConfigPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns true when criteria channel name equals \Spryker\Shared\AiFoundation\AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION.
     *
     * @api
     */
    public function isApplicable(AuditLoggerConfigCriteriaTransfer $auditLoggerConfigCriteriaTransfer): bool
    {
        return $auditLoggerConfigCriteriaTransfer->getChannelName() === $this->getChannelName();
    }

    /**
     * {@inheritDoc}
     * - Returns \Spryker\Shared\AiFoundation\AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION channel name.
     *
     * @api
     */
    public function getChannelName(): string
    {
        return AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION;
    }

    /**
     * {@inheritDoc}
     * - Returns AI interaction audit log handler plugins.
     *
     * @api
     *
     * @return list<\Spryker\Shared\Log\Dependency\Plugin\LogHandlerPluginInterface>
     */
    public function getHandlers(): array
    {
        return $this->getFactory()->getAiInteractionLogHandlerPlugins();
    }

    /**
     * {@inheritDoc}
     * - Returns AI interaction audit log processor plugins.
     *
     * @api
     *
     * @return list<\Spryker\Shared\Log\Dependency\Plugin\LogProcessorPluginInterface>
     */
    public function getProcessors(): array
    {
        return $this->getFactory()->getAiInteractionLogProcessorPlugins();
    }
}
