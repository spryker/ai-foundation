<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\AuditLog;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionTransfer;
use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Generated\Shared\Transfer\AiToolCallTransfer;
use Generated\Shared\Transfer\AuditLoggerConfigCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Shared\AiFoundation\AuditLog\AiInteractionLogContext;
use Spryker\Shared\Log\AuditLoggerTrait;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class AiInteractionLogCollector implements AiInteractionLogCollectorInterface
{
    use AuditLoggerTrait;
    use LoggerTrait;

    protected const string LOG_MESSAGE_AI_INTERACTION = 'AI interaction';

    protected const string LOG_MESSAGE_NOT_COLLECTED = 'AI interaction log entry could not be collected for auditing: %s';

    protected const string METADATA_KEY_CONTEXT = 'context';

    protected const string METADATA_KEY_ERRORS = 'errors';

    protected const string METADATA_KEY_TOOL_NAME = 'tool_name';

    protected const string METADATA_KEY_TOOL_ARGUMENTS = 'tool_arguments';

    protected const string METADATA_KEY_TOOL_RESULT = 'tool_result';

    protected const string METADATA_KEY_IS_EXECUTION_ALLOWED = 'is_execution_allowed';

    protected const string METADATA_FALLBACK = '{}';

    /**
     * Static keeps the buffer of the whole request, as the collector is built new for each plugin
     * call while tool-call entries must survive until the post-prompt flush of the same turn.
     *
     * @var array<\Generated\Shared\Transfer\AiInteractionLogTransfer>
     */
    protected static array $aiInteractionLogTransfers = [];

    public function __construct(
        protected AiFoundationStubInterface $aiFoundationStub
    ) {
    }

    public function collectToolCall(AiToolCallTransfer $aiToolCallTransfer): void
    {
        try {
            $promptRequestTransfer = $aiToolCallTransfer->getPromptRequest();

            $this->addAiInteractionLog(
                (new AiInteractionLogTransfer())
                    ->setConfigurationName($promptRequestTransfer?->getAiConfigurationName())
                    ->setConversationReference($this->resolveConversationReference($promptRequestTransfer))
                    ->setPrompt($promptRequestTransfer?->getPromptMessage()?->getContent())
                    ->setIsSuccessful($aiToolCallTransfer->getIsExecutionAllowed() !== false)
                    ->setMetadata($this->buildToolCallMetadata($aiToolCallTransfer)),
            );
        } catch (Throwable $throwable) {
            $this->logCollectionFailure($throwable);
        }
    }

    public function collectPrompt(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer
    ): void {
        try {
            $this->addAiInteractionLog(
                (new AiInteractionLogTransfer())
                    ->setConfigurationName($promptRequestTransfer->getAiConfigurationName())
                    ->setProvider($promptResponseTransfer->getProvider())
                    ->setModel($promptResponseTransfer->getModel())
                    ->setPrompt($promptRequestTransfer->getPromptMessage()?->getContent())
                    ->setResponse($this->resolveResponseContent($promptResponseTransfer))
                    ->setInputTokens($promptResponseTransfer->getMessage()?->getUsage()?->getInputTokens())
                    ->setOutputTokens($promptResponseTransfer->getMessage()?->getUsage()?->getOutputTokens())
                    ->setConversationReference($this->resolveConversationReference($promptRequestTransfer))
                    ->setInferenceTimeMs($promptResponseTransfer->getInferenceTimeMs())
                    ->setIsSuccessful($promptResponseTransfer->getIsSuccessful() ?? false)
                    ->setMetadata($this->buildPromptMetadata($promptResponseTransfer)),
            );
        } catch (Throwable $throwable) {
            $this->logCollectionFailure($throwable);
        }
    }

    public function flush(): AiInteractionLogCollectionResponseTransfer
    {
        if (static::$aiInteractionLogTransfers === []) {
            return (new AiInteractionLogCollectionResponseTransfer())->setIsSuccessful(true);
        }

        $aiInteractionLogCollectionTransfer = new AiInteractionLogCollectionTransfer();

        foreach (static::$aiInteractionLogTransfers as $aiInteractionLogTransfer) {
            $aiInteractionLogCollectionTransfer->addAiInteractionLog($aiInteractionLogTransfer);
        }

        try {
            return $this->aiFoundationStub->createAiInteractionLogCollection(
                (new AiInteractionLogCollectionRequestTransfer())
                    ->setAiInteractionLogCollection($aiInteractionLogCollectionTransfer),
            );
        } finally {
            $this->clear();
        }
    }

    public function clear(): void
    {
        static::$aiInteractionLogTransfers = [];
    }

    protected function addAiInteractionLog(AiInteractionLogTransfer $aiInteractionLogTransfer): void
    {
        static::$aiInteractionLogTransfers[] = $aiInteractionLogTransfer;

        $this->getAuditLogger(
            (new AuditLoggerConfigCriteriaTransfer())
                ->setChannelName(AiFoundationConstants::AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION),
        )->info(
            static::LOG_MESSAGE_AI_INTERACTION,
            [AiFoundationConstants::AUDIT_LOG_CONTEXT_KEY_TRANSFER => $aiInteractionLogTransfer],
        );
    }

    protected function resolveConversationReference(?PromptRequestTransfer $promptRequestTransfer): ?string
    {
        $conversationReference = (string)$promptRequestTransfer?->getConversationReference();

        return $conversationReference !== '' ? $conversationReference : null;
    }

    protected function resolveResponseContent(PromptResponseTransfer $promptResponseTransfer): ?string
    {
        $messageContent = $promptResponseTransfer->getMessage()?->getContent();

        if ($messageContent !== null && $messageContent !== '') {
            return $messageContent;
        }

        return $promptResponseTransfer->getStreamedContent();
    }

    protected function buildPromptMetadata(PromptResponseTransfer $promptResponseTransfer): string
    {
        $errorMessages = [];

        foreach ($promptResponseTransfer->getErrors() as $errorTransfer) {
            $errorMessages[] = $errorTransfer->getMessage();
        }

        $metadata = [static::METADATA_KEY_CONTEXT => AiInteractionLogContext::PostPrompt->value];

        if ($errorMessages !== []) {
            $metadata[static::METADATA_KEY_ERRORS] = $errorMessages;
        }

        return json_encode($metadata) ?: static::METADATA_FALLBACK;
    }

    protected function buildToolCallMetadata(AiToolCallTransfer $aiToolCallTransfer): string
    {
        $metadata = [
            static::METADATA_KEY_CONTEXT => AiInteractionLogContext::PostToolCall->value,
            static::METADATA_KEY_TOOL_NAME => $aiToolCallTransfer->getToolName(),
            static::METADATA_KEY_TOOL_ARGUMENTS => $aiToolCallTransfer->getToolArguments(),
            static::METADATA_KEY_TOOL_RESULT => $aiToolCallTransfer->getToolResult(),
            static::METADATA_KEY_IS_EXECUTION_ALLOWED => $aiToolCallTransfer->getIsExecutionAllowed(),
        ];

        return json_encode($metadata) ?: static::METADATA_FALLBACK;
    }

    protected function logCollectionFailure(Throwable $throwable): void
    {
        $this->getLogger()->error(
            sprintf(static::LOG_MESSAGE_NOT_COLLECTED, $throwable->getMessage()),
            ['exception' => $throwable],
        );
    }
}
