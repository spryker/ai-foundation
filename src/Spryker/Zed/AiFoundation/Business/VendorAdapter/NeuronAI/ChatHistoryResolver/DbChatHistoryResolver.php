<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver;

use NeuronAI\Chat\History\ChatHistoryInterface;
use Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\ChatHistoryReasoningFilterInterface;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistory\DbChatHistory;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapperInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class DbChatHistoryResolver implements ChatHistoryResolverInterface
{
    protected const string CONTEXT_WINDOW = 'context_window';

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager,
        protected AiFoundationRepositoryInterface $repository,
        protected NeuronAiMessageMapperInterface $messageMapper,
        protected AiFoundationConfig $config,
        protected ?ChatHistoryReasoningFilterInterface $chatHistoryReasoningFilter = null,
    ) {
    }

    /**
     * @param array<string, mixed> $chatHistoryConfiguration
     */
    public function resolve(
        ?string $conversationReference,
        array $chatHistoryConfiguration,
        ?string $providerName = null,
        ?string $modelName = null,
    ): ?ChatHistoryInterface {
        if ($conversationReference === null) {
            return null;
        }

        return $this->createDbChatHistory($conversationReference, $chatHistoryConfiguration, $providerName, $modelName);
    }

    /**
     * @param array<string, mixed> $chatHistoryConfiguration
     */
    protected function createDbChatHistory(
        string $conversationReference,
        array $chatHistoryConfiguration,
        ?string $providerName = null,
        ?string $modelName = null,
    ): DbChatHistory {
        $contextWindow = $chatHistoryConfiguration[static::CONTEXT_WINDOW] ?? $this->config->getConversationHistoryContextWindow();

        return new DbChatHistory(
            entityManager: $this->entityManager,
            repository: $this->repository,
            messageMapper: $this->messageMapper,
            conversationReference: $conversationReference,
            contextWindow: $contextWindow,
            chatHistoryReasoningFilter: $this->chatHistoryReasoningFilter,
            providerName: $providerName,
            modelName: $modelName,
        );
    }
}
