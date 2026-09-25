<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\UsageTransfer;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;

interface NeuronAiMessageMapperInterface
{
    public function mapPromptMessageToUserMessage(PromptMessageTransfer $promptMessageTransfer): UserMessage;

    public function mapUsageToUsageTransfer(?Usage $usage): ?UsageTransfer;
}
