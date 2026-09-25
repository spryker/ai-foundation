<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Shared\AiFoundation\AuditLog;

enum AiInteractionLogContext: string
{
    case PostPrompt = 'POST_PROMPT';
    case PostToolCall = 'POST_TOOL_CALL';
}
