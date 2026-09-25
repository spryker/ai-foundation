<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\ChatSession;

use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;

interface ConversationHistoryDeleterInterface
{
    public function deleteConversationHistoryCollection(
        ConversationHistoryCollectionDeleteCriteriaTransfer $conversationHistoryCollectionDeleteCriteriaTransfer
    ): ConversationHistoryCollectionResponseTransfer;
}
