<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory;

use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;

interface GatewayChatHistoryFactoryInterface
{
    public function createGatewayChatHistory(
        ChatSessionRequestTransfer $chatSessionRequestTransfer,
        ChatSessionResponseTransfer $chatSessionResponseTransfer
    ): GatewayChatHistory;
}
