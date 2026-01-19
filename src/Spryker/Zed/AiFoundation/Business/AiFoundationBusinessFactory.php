<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Business;

use Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemReader;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemReaderInterface;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemWriter;
use Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemWriterInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface getRepository()
 */
class AiFoundationBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemWriterInterface
     */
    public function createAiWorkflowItemWriter(): AiWorkflowItemWriterInterface
    {
        return new AiWorkflowItemWriter(
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Business\AiWorkflow\AiWorkflowItemReaderInterface
     */
    public function createAiWorkflowItemReader(): AiWorkflowItemReaderInterface
    {
        return new AiWorkflowItemReader(
            $this->getRepository(),
        );
    }
}
