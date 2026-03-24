<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Mapper;

use DateTime;
use Generated\Shared\Transfer\AiInteractionLogConditionsTransfer;
use Generated\Shared\Transfer\AiInteractionLogCriteriaTransfer;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Symfony\Component\HttpFoundation\Request;

class AiInteractionLogCriteriaMapper
{
    protected const string PARAM_CONFIGURATION_NAME = 'configuration_name';

    protected const string PARAM_IS_SUCCESSFUL = 'is_successful';

    protected const string PARAM_CONVERSATION_REFERENCE = 'conversation_reference';

    protected const string PARAM_CREATED_AT_FROM = 'created_at_from';

    protected const string PARAM_CREATED_AT_TO = 'created_at_to';

    protected const string DATE_FORMAT = 'Y-m-d';

    public function __construct(protected AiFoundationConfig $config)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function extractFilterDataFromRequest(Request $request): array
    {
        $allowedKeys = [
            static::PARAM_CONFIGURATION_NAME,
            static::PARAM_IS_SUCCESSFUL,
            static::PARAM_CONVERSATION_REFERENCE,
            static::PARAM_CREATED_AT_FROM,
            static::PARAM_CREATED_AT_TO,
        ];

        $filterData = array_intersect_key($request->query->all(), array_flip($allowedKeys));

        if (!isset($filterData[static::PARAM_CREATED_AT_FROM]) && !isset($filterData[static::PARAM_CREATED_AT_TO])) {
            $filterData[static::PARAM_CREATED_AT_FROM] = $this->getDefaultCreatedAtFrom();
        }

        return $filterData;
    }

    public function mapRequestToAiInteractionLogCriteriaTransfer(
        Request $request,
        AiInteractionLogCriteriaTransfer $aiInteractionLogCriteriaTransfer,
    ): AiInteractionLogCriteriaTransfer {
        $conditionsTransfer = new AiInteractionLogConditionsTransfer();

        $configurationName = $request->query->get(static::PARAM_CONFIGURATION_NAME);

        if ($configurationName !== null && $configurationName !== '') {
            $conditionsTransfer->addConfigurationName((string)$configurationName);
        }

        $isSuccessful = $request->query->get(static::PARAM_IS_SUCCESSFUL);

        if ($isSuccessful !== null && $isSuccessful !== '') {
            $conditionsTransfer->setIsSuccessful($isSuccessful === '1');
        }

        $conversationReference = $request->query->get(static::PARAM_CONVERSATION_REFERENCE);

        if ($conversationReference !== null && $conversationReference !== '') {
            $conditionsTransfer->addConversationReference((string)$conversationReference);
        }

        $createdAtFrom = $request->query->get(static::PARAM_CREATED_AT_FROM);
        $createdAtTo = $request->query->get(static::PARAM_CREATED_AT_TO);

        if (($createdAtFrom === null || $createdAtFrom === '') && ($createdAtTo === null || $createdAtTo === '')) {
            $conditionsTransfer->setCreatedAtFrom($this->getDefaultCreatedAtFrom());
        }

        if ($createdAtFrom !== null && $createdAtFrom !== '') {
            $conditionsTransfer->setCreatedAtFrom((string)$createdAtFrom);
        }

        if ($createdAtTo !== null && $createdAtTo !== '') {
            $conditionsTransfer->setCreatedAtTo((string)$createdAtTo);
        }

        $aiInteractionLogCriteriaTransfer->setAiInteractionLogConditions($conditionsTransfer);

        return $aiInteractionLogCriteriaTransfer;
    }

    protected function getDefaultCreatedAtFrom(): string
    {
        $days = $this->config->getAiInteractionLogDefaultDateRangeDays();

        return (new DateTime(sprintf('-%d days', $days)))->format(static::DATE_FORMAT);
    }
}
