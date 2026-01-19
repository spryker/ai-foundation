<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation;

use Codeception\Actor;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class AiFoundationBusinessTester extends Actor
{
    use _generated\AiFoundationBusinessTesterActions;

    /**
     * @param array<string, mixed> $override
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer
     */
    public function haveAiWorkflowItem(array $override = []): AiWorkflowItemTransfer
    {
        $aiWorkflowItemTransfer = (new AiWorkflowItemTransfer())
            ->setName(sprintf('Test Workflow Item %s', rand()))
            ->setFkStateMachineItemState(1)
            ->fromArray($override, true);

        return $this->getLocator()->aiFoundation()->facade()->createAiWorkflowItem($aiWorkflowItemTransfer);
    }
}
