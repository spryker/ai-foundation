<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation;

use Spryker\Client\AiFoundation\Prompt\PromptSender;
use Spryker\Client\AiFoundation\Prompt\PromptSenderInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStub;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

/**
 * @method \Spryker\Client\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationFactory extends AbstractFactory
{
    public function createPromptSender(): PromptSenderInterface
    {
        return new PromptSender($this->createZedAiFoundationStub());
    }

    public function createZedAiFoundationStub(): AiFoundationStubInterface
    {
        return new AiFoundationStub($this->getZedRequestClient());
    }

    public function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::CLIENT_ZED_REQUEST);
    }
}
