<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\ContextBuilder;

use Generated\Shared\Transfer\AiInteractionLogTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class AiInteractionLogContextBuilder implements AiInteractionLogContextBuilderInterface
{
    protected const string METADATA_KEY_TOOL_INVOCATIONS = 'tool_invocations';

    protected const string METADATA_KEY_ERRORS = 'errors';

    protected const string METADATA_KEY_STRUCTURED_SCHEMA_CLASS = 'structured_schema_class';

    public function buildPostPromptContext(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): AiInteractionLogTransfer {
        return (new AiInteractionLogTransfer())
            ->setConfigurationName($promptRequestTransfer->getAiConfigurationName())
            ->setProvider($promptResponseTransfer->getProvider())
            ->setModel($promptResponseTransfer->getModel())
            ->setPrompt($promptRequestTransfer->getPromptMessageOrFail()->getContent())
            ->setResponse($this->resolveResponseContent($promptResponseTransfer))
            ->setInputTokens($promptResponseTransfer->getMessage()?->getUsage()?->getInputTokens())
            ->setOutputTokens($promptResponseTransfer->getMessage()?->getUsage()?->getOutputTokens())
            ->setConversationReference($promptRequestTransfer->getConversationReference())
            ->setInferenceTimeMs($promptResponseTransfer->getInferenceTimeMs())
            ->setIsSuccessful($promptResponseTransfer->getIsSuccessful() ?? false)
            ->setMetadata($this->buildMetadata($promptRequestTransfer, $promptResponseTransfer));
    }

    protected function resolveResponseContent(PromptResponseTransfer $promptResponseTransfer): ?string
    {
        $structuredMessage = $promptResponseTransfer->getStructuredMessage();

        if ($structuredMessage instanceof AbstractTransfer) {
            return json_encode($structuredMessage->toArray()) ?: null;
        }

        return $promptResponseTransfer->getMessage()?->getContent();
    }

    protected function buildMetadata(
        PromptRequestTransfer $promptRequestTransfer,
        PromptResponseTransfer $promptResponseTransfer,
    ): string {
        $metadata = [];

        foreach ($promptResponseTransfer->getToolInvocations() as $toolInvocation) {
            $metadata[static::METADATA_KEY_TOOL_INVOCATIONS][] = [
                'name' => $toolInvocation->getName(),
                'arguments' => $toolInvocation->getArguments(),
                'result' => $toolInvocation->getResult(),
            ];
        }

        foreach ($promptResponseTransfer->getErrors() as $error) {
            $metadata[static::METADATA_KEY_ERRORS][] = $error->getMessage();
        }

        if ($promptRequestTransfer->getStructuredMessage() !== null) {
            $metadata[static::METADATA_KEY_STRUCTURED_SCHEMA_CLASS] = $promptRequestTransfer->getStructuredMessage()::class;
        }

        return json_encode($metadata) ?: '{}';
    }
}
