<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Chat\Messages\Message;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

interface NeuronAiMessageMapperInterface
{
    public function mapPromptMessageToProviderMessage(PromptMessageTransfer $promptMessageTransfer): Message;

    public function mapProviderResponseToPromptResponse(Message $message): PromptResponseTransfer;

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $structuredResponseTransfer
     *
     * @return array<string, mixed>
     */
    public function mapTransferToStructuredResponseFormat(AbstractTransfer $structuredResponseTransfer): array;

    public function mapProviderStructuredResponseToTransfer(Message $message, AbstractTransfer $structuredResponseTransfer): AbstractTransfer;
}
