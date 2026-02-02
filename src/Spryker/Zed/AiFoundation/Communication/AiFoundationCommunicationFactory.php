<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication;

use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getEntityManager()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationPersistenceFactory getRepository()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\CommandPluginInterface>
     */
    public function getAiWorkflowCommandPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_WORKFLOW_COMMAND);
    }

    /**
     * @return array<\Spryker\Zed\StateMachine\Dependency\Plugin\ConditionPluginInterface>
     */
    public function getAiWorkflowConditionPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_WORKFLOW_CONDITION);
    }
}
