<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use NeuronAI\Chat\Messages\Message;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

interface NeuronAiMessageMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\PromptMessageTransfer $promptMessageTransfer
     *
     * @return \NeuronAI\Chat\Messages\Message
     */
    public function mapPromptMessageToProviderMessage(PromptMessageTransfer $promptMessageTransfer): Message;

    /**
     * @param \NeuronAI\Chat\Messages\Message $message
     *
     * @return \Generated\Shared\Transfer\PromptResponseTransfer
     */
    public function mapProviderResponseToPromptResponse(Message $message): PromptResponseTransfer;

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $structuredResponseTransfer
     *
     * @return array<string, mixed>
     */
    public function mapTransferToStructuredResponseFormat(AbstractTransfer $structuredResponseTransfer): array;

    /**
     * @template T of \Spryker\Shared\Kernel\Transfer\AbstractTransfer
     *
     * @phpstan-return T
     *
     * @param \NeuronAI\Chat\Messages\Message $message
     * @param T $structuredResponseTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\AbstractTransfer
     */
    public function mapProviderStructuredResponseToTransfer(Message $message, AbstractTransfer $structuredResponseTransfer): AbstractTransfer;
}
