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
     * @param array<string, mixed> $chatHistoryConfiguration
     */
    public function resolve(?string $conversationReference, array $chatHistoryConfiguration): ?ChatHistoryInterface;
}
