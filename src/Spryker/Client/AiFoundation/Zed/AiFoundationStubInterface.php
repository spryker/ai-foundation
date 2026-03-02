<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Zed;

use Generated\Shared\Transfer\ConversationHistoryCollectionTransfer;
use Generated\Shared\Transfer\ConversationHistoryCriteriaTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;

interface AiFoundationStubInterface
{
    public function prompt(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer;

    public function getConversationHistoryCollection(
        ConversationHistoryCriteriaTransfer $conversationHistoryCriteriaTransfer
    ): ConversationHistoryCollectionTransfer;
}
