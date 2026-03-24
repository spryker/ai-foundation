<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\Prompt;

use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class PromptSender implements PromptSenderInterface
{
    public function __construct(protected readonly AiFoundationStubInterface $aiFoundationStub)
    {
    }

    public function sendPrompt(PromptRequestTransfer $promptRequestTransfer): PromptResponseTransfer
    {
        $structuredMessageClass = $promptRequestTransfer->getStructuredMessage() instanceof AbstractTransfer
            ? get_class($promptRequestTransfer->getStructuredMessage())
            : null;

        $promptRequestTransfer->setStructuredMessageClass($structuredMessageClass);

        $promptResponseTransfer = $this->aiFoundationStub->prompt($promptRequestTransfer);

        if ($structuredMessageClass === null) {
            return $promptResponseTransfer;
        }

        $structuredMessage = $promptResponseTransfer->offsetGet(PromptResponseTransfer::STRUCTURED_MESSAGE);

        if (is_array($structuredMessage)) {
            $promptResponseTransfer->setStructuredMessage((new $structuredMessageClass())->fromArray($structuredMessage));
        }

        return $promptResponseTransfer;
    }
}
