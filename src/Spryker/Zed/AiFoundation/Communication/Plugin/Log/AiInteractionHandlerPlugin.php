<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Plugin\Log;

use Monolog\Handler\HandlerInterface;
use Spryker\Zed\Log\Communication\Plugin\Handler\AbstractHandlerPlugin;

/**
 * Provides a database-backed Monolog handler for persisting AI interaction logs.
 *
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiInteractionHandlerPlugin extends AbstractHandlerPlugin
{
    protected function getHandler(): HandlerInterface
    {
        if (!$this->handler) {
            $this->handler = $this->getFactory()->createAiInteractionDbHandler();
        }

        return $this->handler;
    }
}
