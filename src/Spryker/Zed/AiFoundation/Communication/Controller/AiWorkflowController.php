<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Controller;

use Generated\Shared\Transfer\AiWorkflowItemConditionsTransfer;
use Generated\Shared\Transfer\AiWorkflowItemCriteriaTransfer;
use Generated\Shared\Transfer\AiWorkflowItemTransfer;
use Generated\Shared\Transfer\StateMachineItemTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 */
class AiWorkflowController extends AbstractController
{
    protected const string CSRF_TOKEN_ID = 'AiWorkflowManualEventTrigger';

    protected const string CSRF_TOKEN_PARAM = '_token';

    protected const string URL_WORKFLOW_LIST = '/ai-foundation/ai-workflow';

    protected const string URL_WORKFLOW_DETAIL = '/ai-foundation/ai-workflow/detail?id=%d';

    protected const string PARAM_IDENTIFIER = 'identifier';

    protected const string PARAM_ID_ITEM_STATE = 'idItemState';

    protected const string PARAM_EVENT_NAME = 'eventName';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(): array
    {
        $aiWorkflowItemTable = $this->getFactory()->createAiWorkflowItemTable();

        return $this->viewResponse([
            'aiWorkflowItemTable' => $aiWorkflowItemTable->render(),
        ]);
    }

    public function tableAction(): JsonResponse
    {
        $aiWorkflowItemTable = $this->getFactory()->createAiWorkflowItemTable();

        return $this->jsonResponse($aiWorkflowItemTable->fetchData());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function detailAction(Request $request): array|RedirectResponse
    {
        $idAiWorkflowItem = $this->castId($request->query->get('id'));

        $aiWorkflowItemTransfer = $this->findAiWorkflowItem($idAiWorkflowItem);

        if ($aiWorkflowItemTransfer === null) {
            $this->addErrorMessage('AI workflow item with ID %d not found.', ['%d' => $idAiWorkflowItem]);

            return $this->redirectResponse(static::URL_WORKFLOW_LIST);
        }

        $processedItem = null;
        $manualEvents = [];
        $stateHistory = [];

        if ($aiWorkflowItemTransfer->getFkStateMachineItemState() !== null) {
            $stateMachineItemTransfer = (new StateMachineItemTransfer())
                ->setIdentifier($idAiWorkflowItem)
                ->setIdItemState($aiWorkflowItemTransfer->getFkStateMachineItemState());

            $processedItem = $this->getFactory()
                ->getStateMachineFacade()
                ->getProcessedStateMachineItemTransfer($stateMachineItemTransfer);

            $manualEvents = $this->getFactory()->getStateMachineFacade()->getManualEventsForStateMachineItem($processedItem);

            $stateHistory = $this->getFactory()->getStateMachineFacade()->getStateHistoryByStateItemIdentifier(
                $processedItem->getIdStateMachineProcessOrFail(),
                $idAiWorkflowItem,
            );
        }

        return $this->viewResponse([
            'aiWorkflowItem' => $aiWorkflowItemTransfer,
            'stateMachineItem' => $processedItem,
            'stateHistory' => $stateHistory,
            'manualEvents' => $manualEvents,
        ]);
    }

    protected function findAiWorkflowItem(int $idAiWorkflowItem): ?AiWorkflowItemTransfer
    {
        $aiWorkflowItemConditionsTransfer = (new AiWorkflowItemConditionsTransfer())
            ->addAiWorkflowItemId($idAiWorkflowItem);

        $aiWorkflowItemCriteriaTransfer = (new AiWorkflowItemCriteriaTransfer())
            ->setAiWorkflowItemConditions($aiWorkflowItemConditionsTransfer);

        $aiWorkflowItemCollectionTransfer = $this->getFacade()->getAiWorkflowItemCollection($aiWorkflowItemCriteriaTransfer);

        $aiWorkflowItems = $aiWorkflowItemCollectionTransfer->getAiWorkflowItems();

        if ($aiWorkflowItems->count() === 0) {
            return null;
        }

        return $aiWorkflowItems->offsetGet(0);
    }

    public function triggerManualEventAction(Request $request): RedirectResponse
    {
        $idAiWorkflowItem = $this->castId($request->request->get(static::PARAM_IDENTIFIER));

        $redirectUrl = sprintf(static::URL_WORKFLOW_DETAIL, $idAiWorkflowItem);

        if (!$request->isMethod(Request::METHOD_POST)) {
            $this->addErrorMessage('Invalid request.');

            return $this->redirectResponse($redirectUrl);
        }

        if (!$this->isValidCsrfToken((string)$request->request->get(static::CSRF_TOKEN_PARAM))) {
            $this->addErrorMessage('Invalid or missing csrf token.');

            return $this->redirectResponse($redirectUrl);
        }

        $idItemState = $this->castId($request->request->get(static::PARAM_ID_ITEM_STATE));
        $eventName = (string)$request->request->get(static::PARAM_EVENT_NAME);

        if (!$this->isEventAllowed($eventName, $idItemState)) {
            $this->addErrorMessage('Event "%s" is not allowed for the current state.', ['%s' => $eventName]);

            return $this->redirectResponse($redirectUrl);
        }

        $stateMachineItemTransfer = (new StateMachineItemTransfer())
            ->setIdentifier($idAiWorkflowItem)
            ->setIdItemState($idItemState);

        $triggeredTransitions = $this->getFactory()
            ->getStateMachineFacade()
            ->triggerEvent($eventName, $stateMachineItemTransfer);

        if ($triggeredTransitions === 0) {
            $this->addErrorMessage('Event "%s" could not be triggered.', ['%s' => $eventName]);

            return $this->redirectResponse($redirectUrl);
        }

        $this->addSuccessMessage('Event "%s" triggered successfully.', ['%s' => $eventName]);

        return $this->redirectResponse($redirectUrl);
    }

    protected function isEventAllowed(string $eventName, int $idItemState): bool
    {
        $stateMachineItemTransfer = (new StateMachineItemTransfer())
            ->setIdItemState($idItemState);

        $processedItem = $this->getFactory()
            ->getStateMachineFacade()
            ->getProcessedStateMachineItemTransfer($stateMachineItemTransfer);

        $manualEvents = $this->getFactory()
            ->getStateMachineFacade()
            ->getManualEventsForStateMachineItem($processedItem);

        return in_array($eventName, $manualEvents, true);
    }

    protected function isValidCsrfToken(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        return $this->getFactory()
            ->getCsrfTokenManager()
            ->isTokenValid(new CsrfToken(static::CSRF_TOKEN_ID, $token));
    }
}
