<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver;

use NeuronAI\Chat\History\ChatHistoryInterface;

interface ChatHistoryResolverInterface
{
    /**
     * @param string|null $conversationReference
     * @param array<string, mixed> $chatHistoryConfiguration
     *
     * @return \NeuronAI\Chat\History\ChatHistoryInterface|null
     */
    public function resolve(?string $conversationReference, array $chatHistoryConfiguration): ?ChatHistoryInterface;
}
