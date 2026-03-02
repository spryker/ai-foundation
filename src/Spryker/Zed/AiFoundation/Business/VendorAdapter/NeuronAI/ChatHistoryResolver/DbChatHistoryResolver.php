<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver;

use NeuronAI\Chat\History\ChatHistoryInterface;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistory\DbChatHistory;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface;

class DbChatHistoryResolver implements ChatHistoryResolverInterface
{
    protected const string CONTEXT_WINDOW = 'context_window';

    public function __construct(
        protected AiFoundationEntityManagerInterface $entityManager,
        protected AiFoundationRepositoryInterface $repository,
        protected NeuronAiMessageMapper $messageMapper,
        protected AiFoundationConfig $config,
    ) {
    }

    /**
     * @param string|null $conversationReference
     * @param array<string, mixed> $chatHistoryConfiguration
     *
     * @return \NeuronAI\Chat\History\ChatHistoryInterface|null
     */
    public function resolve(?string $conversationReference, array $chatHistoryConfiguration): ?ChatHistoryInterface
    {
        if ($conversationReference === null) {
            return null;
        }

        return $this->createDbChatHistory($conversationReference, $chatHistoryConfiguration);
    }

    /**
     * @param string $conversationReference
     * @param array<string, mixed> $chatHistoryConfiguration
     *
     * @return \Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistory\DbChatHistory
     */
    protected function createDbChatHistory(string $conversationReference, array $chatHistoryConfiguration): DbChatHistory
    {
        $contextWindow = $chatHistoryConfiguration[static::CONTEXT_WINDOW] ?? $this->config->getConversationHistoryContextWindow();

        return new DbChatHistory(
            entityManager: $this->entityManager,
            repository: $this->repository,
            messageMapper: $this->messageMapper,
            conversationReference: $conversationReference,
            contextWindow: $contextWindow,
        );
    }
}
