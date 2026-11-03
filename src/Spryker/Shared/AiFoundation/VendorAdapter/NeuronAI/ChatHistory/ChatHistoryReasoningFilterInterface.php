<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Shared\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use NeuronAI\Chat\Messages\Message;

interface ChatHistoryReasoningFilterInterface
{
    /**
     * Specification:
     * - Marks an assistant message with the AI provider name and the model that produced it.
     * - Leaves messages of other roles unchanged.
     */
    public function markMessageOrigin(Message $message, string $providerName, ?string $modelName): Message;

    /**
     * Specification:
     * - Removes reasoning content blocks from serialized messages that were not produced by the given AI provider and model.
     * - Removes reasoning content blocks from serialized messages without an origin mark.
     * - Removes provider reasoning metadata (redacted reasoning, thought signatures, reasoning content) from these messages and their content blocks.
     * - Recalculates the provider tool positions of these messages so that tool calls keep their order among the remaining content blocks.
     * - Keeps messages of the given AI provider and model unchanged.
     * - Keeps all other content blocks and messages unchanged.
     *
     * @param array<int, array<string, mixed>> $serializedMessages
     *
     * @return array<int, array<string, mixed>>
     */
    public function removeForeignReasoning(array $serializedMessages, string $providerName, ?string $modelName): array;
}
