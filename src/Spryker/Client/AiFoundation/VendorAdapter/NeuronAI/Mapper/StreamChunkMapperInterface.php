<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper;

use Generated\Shared\Transfer\PromptStreamChunkTransfer;
use NeuronAI\Chat\Messages\Stream\Chunks\StreamChunk;

interface StreamChunkMapperInterface
{
    public function mapStreamChunkToPromptStreamChunk(StreamChunk $streamChunk): ?PromptStreamChunkTransfer;
}
