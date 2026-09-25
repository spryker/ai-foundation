<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\ChatSession;

use Generated\Shared\Transfer\AiConfigurationTransfer;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Business\ChatSession\Exception\AiConfigurationNotResolvableException;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class ChatSessionInitializer implements ChatSessionInitializerInterface
{
    protected const string KEY_PROVIDER_NAME = 'provider_name';

    protected const string KEY_PROVIDER_CONFIG = 'provider_config';

    protected const string KEY_SYSTEM_PROMPT = 'system_prompt';

    protected const string ERROR_MESSAGE_CONFIGURATION_NOT_RESOLVABLE = 'AI configuration "%s" is not resolvable by a client.';

    public function __construct(
        protected AiFoundationRepositoryInterface $repository,
        protected AiFoundationConfig $config
    ) {
    }

    public function initChatSession(ChatSessionRequestTransfer $chatSessionRequestTransfer): ChatSessionResponseTransfer
    {
        $chatSessionResponseTransfer = (new ChatSessionResponseTransfer())
            ->setIsSuccessful(false)
            ->setContextWindow($this->config->getConversationHistoryContextWindow());

        $aiConfigurationName = (string)$chatSessionRequestTransfer->getAiConfigurationName();

        try {
            $aiConfigurationTransfer = $this->resolveAiConfiguration($aiConfigurationName);
        } catch (AiConfigurationNotResolvableException $exception) {
            return $chatSessionResponseTransfer->addError(
                (new ErrorTransfer())->setMessage($exception->getMessage()),
            );
        }

        return $chatSessionResponseTransfer
            ->setIsSuccessful(true)
            ->setAiConfiguration($aiConfigurationTransfer)
            ->setConversationHistory($this->findConversationHistory($chatSessionRequestTransfer));
    }

    /**
     * @throws \Spryker\Zed\AiFoundation\Business\ChatSession\Exception\AiConfigurationNotResolvableException
     */
    protected function resolveAiConfiguration(string $aiConfigurationName): AiConfigurationTransfer
    {
        if (!in_array($aiConfigurationName, $this->config->getClientResolvableAiConfigurationNames(), true)) {
            throw new AiConfigurationNotResolvableException(
                sprintf(static::ERROR_MESSAGE_CONFIGURATION_NOT_RESOLVABLE, $aiConfigurationName),
            );
        }

        $aiConfigurations = $this->config->getAiConfigurations();

        if (!isset($aiConfigurations[$aiConfigurationName])) {
            throw new AiConfigurationNotResolvableException(
                sprintf(static::ERROR_MESSAGE_CONFIGURATION_NOT_RESOLVABLE, $aiConfigurationName),
            );
        }

        $aiConfiguration = $aiConfigurations[$aiConfigurationName];

        if (empty($aiConfiguration[static::KEY_PROVIDER_NAME]) || empty($aiConfiguration[static::KEY_PROVIDER_CONFIG])) {
            throw new AiConfigurationNotResolvableException(
                sprintf(static::ERROR_MESSAGE_CONFIGURATION_NOT_RESOLVABLE, $aiConfigurationName),
            );
        }

        return (new AiConfigurationTransfer())
            ->setName($aiConfigurationName)
            ->setProviderName((string)$aiConfiguration[static::KEY_PROVIDER_NAME])
            ->setProviderConfig((array)$aiConfiguration[static::KEY_PROVIDER_CONFIG])
            ->setSystemPrompt(
                isset($aiConfiguration[static::KEY_SYSTEM_PROMPT]) ? (string)$aiConfiguration[static::KEY_SYSTEM_PROMPT] : null,
            );
    }

    protected function findConversationHistory(
        ChatSessionRequestTransfer $chatSessionRequestTransfer
    ): ?ConversationHistoryTransfer {
        $conversationReference = $chatSessionRequestTransfer->getConversationReference();

        if ($conversationReference === null || $conversationReference === '') {
            return null;
        }

        $conversationHistoryCriteriaTransfer = (new ConversationHistoryCriteriaTransfer())
            ->setConversationHistoryConditions(
                (new ConversationHistoryConditionsTransfer())->setConversationReferences([$conversationReference]),
            );

        $conversationHistoryCollectionTransfer = $this->repository->getConversationHistoryCollection(
            $conversationHistoryCriteriaTransfer,
        );

        if ($conversationHistoryCollectionTransfer->getConversationHistories()->count() === 0) {
            return null;
        }

        return $conversationHistoryCollectionTransfer->getConversationHistories()->offsetGet(0);
    }
}
