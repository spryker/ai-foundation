<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Plugin\StateMachine\Command;

use Exception;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\StateMachine\Dependency\Plugin\CommandPluginInterface;

/**
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 */
class OryxAgentAnalyzeCommandPlugin extends AbstractPlugin implements CommandPluginInterface
{
    protected const string CONTEXT_KEY_PROMPT = 'prompt';

    protected const string CONTEXT_KEY_ANALYSIS_RESULT = 'analysis_result';

    protected const string CONTEXT_KEY_SUCCESS = 'success';

    protected const string CONTEXT_KEY_ERROR = 'error';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\StateMachineItemTransfer $stateMachineItemTransfer
     *
     * @return void
     */
    public function run(StateMachineItemTransfer $stateMachineItemTransfer): void
    {
        $stateMachineItemTransfer->getIdentifierOrFail();

        $aiWorkflowItemTransfer = $this->loadWorkflowItem($stateMachineItemTransfer);

        if ($aiWorkflowItemTransfer === null) {
            return;
        }

        $this->executeAnalysis($aiWorkflowItemTransfer);

        $this->getFacade()->updateAiWorkflowItemContext($aiWorkflowItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\StateMachineItemTransfer $stateMachineItemTransfer
     *
     * @return \Generated\Shared\Transfer\AiWorkflowItemTransfer|null
     */
    protected function loadWorkflowItem(StateMachineItemTransfer $stateMachineItemTransfer): ?AiWorkflowItemTransfer
    {
        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->addAiWorkflowItemId($stateMachineItemTransfer->getIdentifierOrFail());

        $aiWorkflowItemCollection = $this->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        return $aiWorkflowItemCollection->getAiWorkflowItems()->count() > 0
            ? $aiWorkflowItemCollection->getAiWorkflowItems()->offsetGet(0)
            : null;
    }

    /**
     * @param \Generated\Shared\Transfer\AiWorkflowItemTransfer $aiWorkflowItemTransfer
     *
     * @return void
     */
    protected function executeAnalysis(AiWorkflowItemTransfer $aiWorkflowItemTransfer): void
    {
        /** @var array<string, mixed> $contextData */
        $contextData = $aiWorkflowItemTransfer->getContextData();

        try {
            $prompt = $contextData[static::CONTEXT_KEY_PROMPT] ?? 'Analyze the given task and provide your insights.';

            $promptRequestTransfer = new PromptRequestTransfer();
            $promptRequestTransfer->setPromptMessage((new PromptMessageTransfer())->setContent($prompt));

            $promptResponseTransfer = $this->getFactory()->getClientAiFoundation()->prompt($promptRequestTransfer);

            if ($promptResponseTransfer->getIsSuccessful() && $promptResponseTransfer->getMessage() !== null) {
                $analysisResult = sprintf(
                    'Analysis completed for: %s. This is a placeholder response.',
                    $promptResponseTransfer->getMessage()->getContent(),
                );
                $contextData[static::CONTEXT_KEY_ANALYSIS_RESULT] = $analysisResult;
                $contextData[static::CONTEXT_KEY_SUCCESS] = $promptResponseTransfer->getIsSuccessful();
            }

            if (!$promptResponseTransfer->getIsSuccessful()) {
                $contextData[static::CONTEXT_KEY_SUCCESS] = false;

                /** @var \Generated\Shared\Transfer\ErrorTransfer $error */
                $error = $promptResponseTransfer->getErrors()->getIterator()->current();
                $contextData[static::CONTEXT_KEY_ERROR] = $error->getMessage();
            }
        } catch (Exception $exception) {
            $contextData[static::CONTEXT_KEY_SUCCESS] = false;
            $contextData[static::CONTEXT_KEY_ERROR] = $exception->getMessage();
        }

        $aiWorkflowItemTransfer->setContextData($contextData);
    }
}
