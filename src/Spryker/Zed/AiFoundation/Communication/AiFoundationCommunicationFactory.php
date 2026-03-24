<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication;

use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Communication\Form\AiInteractionLogTableFilterForm;
use Spryker\Zed\AiFoundation\Communication\Form\DataProvider\AiInteractionLogTableFilterFormDataProvider;
use Spryker\Zed\AiFoundation\Communication\Log\AiInteractionDbHandler;
use Spryker\Zed\AiFoundation\Communication\Mapper\AiInteractionLogCriteriaMapper;
use Spryker\Zed\AiFoundation\Communication\Table\AiInteractionLogTable;
use Spryker\Zed\AiFoundation\Communication\Table\AiWorkflowItemTable;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\StateMachine\Business\StateMachineFacadeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 */
class AiFoundationCommunicationFactory extends AbstractCommunicationFactory
{
    public function createAiInteractionDbHandler(): AiInteractionDbHandler
    {
        return new AiInteractionDbHandler(
            aiFoundationFacade: $this->getFacade(),
        );
    }

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

    public function createAiWorkflowItemTable(): AiWorkflowItemTable
    {
        return new AiWorkflowItemTable();
    }

    public function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::SERVICE_FORM_CSRF_PROVIDER);
    }

    public function getStateMachineFacade(): StateMachineFacadeInterface
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::FACADE_STATE_MACHINE);
    }

    /**
     * @return list<\Spryker\Shared\Log\Dependency\Plugin\LogHandlerPluginInterface>
     */
    public function getAiInteractionLogHandlerPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_INTERACTION_LOG_HANDLER);
    }

    /**
     * @return list<\Spryker\Shared\Log\Dependency\Plugin\LogProcessorPluginInterface>
     */
    public function getAiInteractionLogProcessorPlugins(): array
    {
        return $this->getProvidedDependency(AiFoundationDependencyProvider::PLUGINS_AI_INTERACTION_LOG_PROCESSOR);
    }

    public function createAiInteractionLogTable(): AiInteractionLogTable
    {
        return new AiInteractionLogTable();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    public function createAiInteractionLogTableFilterForm(array $data = [], array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(AiInteractionLogTableFilterForm::class, $data, $options);
    }

    public function createAiInteractionLogTableFilterFormDataProvider(): AiInteractionLogTableFilterFormDataProvider
    {
        return new AiInteractionLogTableFilterFormDataProvider($this->getConfig());
    }

    public function createAiInteractionLogCriteriaMapper(): AiInteractionLogCriteriaMapper
    {
        return new AiInteractionLogCriteriaMapper($this->getConfig());
    }
}
