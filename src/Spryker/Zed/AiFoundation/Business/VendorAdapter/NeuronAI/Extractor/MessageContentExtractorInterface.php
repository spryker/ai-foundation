<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor;

use NeuronAI\Chat\Messages\Message;

interface MessageContentExtractorInterface
{
    public function extractFinalText(Message $message): ?string;

    public function extractReasoning(Message $message): ?string;
}
