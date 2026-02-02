<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Spryker\Zed\AiFoundation\Business\AiWorkflow\Creator\AiWorkflowItemCreator;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Creator\AiWorkflowItemCreatorInterface;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Deleter\AiWorkflowItemDeleter;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Deleter\AiWorkflowItemDeleterInterface;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Reader\AiWorkflowItemReader;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Reader\AiWorkflowItemReaderInterface;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemContextUpdater;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemContextUpdaterInterface;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemStateUpdater;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemStateUpdaterInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface getRepository()
 */
class AiFoundationBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\Creator\AiWorkflowItemCreatorInterface
     */
    public function createAiWorkflowItemCreator(): AiWorkflowItemCreatorInterface
    {
        return new AiWorkflowItemCreator(
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemStateUpdaterInterface
     */
    public function createAiWorkflowItemStateUpdater(): AiWorkflowItemStateUpdaterInterface
    {
        return new AiWorkflowItemStateUpdater(
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\Updater\AiWorkflowItemContextUpdaterInterface
     */
    public function createAiWorkflowItemContextUpdater(): AiWorkflowItemContextUpdaterInterface
    {
        return new AiWorkflowItemContextUpdater(
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\Deleter\AiWorkflowItemDeleterInterface
     */
    public function createAiWorkflowItemDeleter(): AiWorkflowItemDeleterInterface
    {
        return new AiWorkflowItemDeleter(
            $this->getRepository(),
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\Reader\AiWorkflowItemReaderInterface
     */
    public function createAiWorkflowItemReader(): AiWorkflowItemReaderInterface
    {
        return new AiWorkflowItemReader(
            $this->getRepository(),
        );
    }
}
