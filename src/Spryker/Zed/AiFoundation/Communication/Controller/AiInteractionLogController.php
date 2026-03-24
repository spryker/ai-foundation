<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Controller;

use Generated\Shared\Transfer\AiInteractionLogConditionsTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method \Spryker\Zed\AiFoundation\Communication\AiFoundationCommunicationFactory getFactory()
 * @method \Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface getFacade()
 */
class AiInteractionLogController extends AbstractController
{
    protected const string PARAM_ID = 'id';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(Request $request): array
    {
        $filterFormDataProvider = $this->getFactory()->createAiInteractionLogTableFilterFormDataProvider();

        $filterForm = $this->getFactory()->createAiInteractionLogTableFilterForm(
            $filterFormDataProvider->getData(),
            $filterFormDataProvider->getOptions(),
        );

        $filterForm->handleRequest($request);

        $filterData = $filterForm->getData() ?? [];

        $aiInteractionLogTable = $this->getFactory()->createAiInteractionLogTable();
        $aiInteractionLogTable->applyCriteria($filterData);

        return $this->viewResponse([
            'aiInteractionLogTable' => $aiInteractionLogTable->render(),
            'filterForm' => $filterForm->createView(),
        ]);
    }

    public function tableAction(Request $request): JsonResponse
    {
        $filterData = $this->getFactory()
            ->createAiInteractionLogCriteriaMapper()
            ->extractFilterDataFromRequest($request);

        $aiInteractionLogTable = $this->getFactory()->createAiInteractionLogTable();
        $aiInteractionLogTable->applyCriteria($filterData);

        return $this->jsonResponse($aiInteractionLogTable->fetchData());
    }

    public function statsAction(Request $request): Response
    {
        $aiInteractionLogCriteriaTransfer = $this->getFactory()
            ->createAiInteractionLogCriteriaMapper()
            ->mapRequestToAiInteractionLogCriteriaTransfer(
                $request,
                new AiInteractionLogCriteriaTransfer(),
            );

        $aggregationTransfer = $this->getFacade()->getAiInteractionLogAggregation($aiInteractionLogCriteriaTransfer);

        return $this->renderView('@AiFoundation/Partials/ai-interaction-log-stats-cards.twig', [
            'aggregation' => $aggregationTransfer,
        ]);
    }

    public function detailAction(Request $request): Response
    {
        $idAiInteractionLog = $this->castId($request->query->get(static::PARAM_ID));

        $aiInteractionLogCriteriaTransfer = (new AiInteractionLogCriteriaTransfer())
            ->setAiInteractionLogConditions(
                (new AiInteractionLogConditionsTransfer())
                    ->addAiInteractionLogId($idAiInteractionLog),
            );

        $responseTransfer = $this->getFacade()
            ->getAiInteractionLogCollection($aiInteractionLogCriteriaTransfer);

        $aiInteractionLogs = $responseTransfer->getAiInteractionLogCollectionOrFail()->getAiInteractionLogs();

        if ($aiInteractionLogs->count() === 0) {
            return $this->renderView('@AiFoundation/Partials/ai-interaction-log-detail-content.twig', [
                'notFound' => true,
            ]);
        }

        return $this->renderView('@AiFoundation/Partials/ai-interaction-log-detail-content.twig', [
            'log' => $aiInteractionLogs->offsetGet(0),
        ]);
    }
}
