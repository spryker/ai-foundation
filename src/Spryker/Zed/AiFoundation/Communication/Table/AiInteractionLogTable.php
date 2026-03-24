<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Table;

use DateTime;
use DateTimeInterface;
use Orm\Zed\AiFoundation\Persistence\Map\SpyAiInteractionLogTableMap;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

/**
 * @module AiFoundation
 */
class AiInteractionLogTable extends AbstractTable
{
    protected const string COLUMN_TOTAL_TOKENS = 'total_tokens';

    protected const int PROMPT_TRUNCATE_LENGTH = 100;

    protected const int DEFAULT_PAGE_LENGTH = 25;

    protected const string BADGE_SUCCESS_TEMPLATE = '<span class="label label-success">%s</span>';

    protected const string BADGE_FAILED_TEMPLATE = '<span class="label label-danger">%s</span>';

    protected const string FILTER_CONFIGURATION_NAME = 'configuration_name';

    protected const string FILTER_IS_SUCCESSFUL = 'is_successful';

    protected const string FILTER_CONVERSATION_REFERENCE = 'conversation_reference';

    protected const string FILTER_CREATED_AT_FROM = 'created_at_from';

    protected const string FILTER_CREATED_AT_TO = 'created_at_to';

    /**
     * @var array<string, mixed>
     */
    protected array $filterData = [];

    /**
     * @param array<string, mixed> $filterData
     */
    public function applyCriteria(array $filterData): void
    {
        $this->filterData = $this->normalizeFilterData($filterData);
    }

    /**
     * @param array<string, mixed> $filterData
     *
     * @return array<string, mixed>
     */
    protected function normalizeFilterData(array $filterData): array
    {
        foreach ($filterData as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $filterData[$key] = $value->format('Y-m-d');
            }
        }

        return $filterData;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader([
            SpyAiInteractionLogTableMap::COL_PROMPT => 'Prompt',
            SpyAiInteractionLogTableMap::COL_CONVERSATION_REFERENCE => 'Conversation',
            SpyAiInteractionLogTableMap::COL_PROVIDER => 'Provider',
            SpyAiInteractionLogTableMap::COL_MODEL => 'Model',
            static::COLUMN_TOTAL_TOKENS => 'Total Tokens',
            SpyAiInteractionLogTableMap::COL_CONFIGURATION_NAME => 'Configuration',
            SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL => 'Status',
            SpyAiInteractionLogTableMap::COL_INFERENCE_TIME_MS => 'Inference (ms)',
            SpyAiInteractionLogTableMap::COL_CREATED_AT => 'Created At',
        ]);

        $config->setSortable([
            SpyAiInteractionLogTableMap::COL_CONFIGURATION_NAME,
            SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL,
            SpyAiInteractionLogTableMap::COL_CREATED_AT,
        ]);

        $config->setDefaultSortField(SpyAiInteractionLogTableMap::COL_CREATED_AT, TableConfiguration::SORT_DESC);
        $config->setPageLength(static::DEFAULT_PAGE_LENGTH);

        $config->addRawColumn(SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL);
        $config->addRawColumn(SpyAiInteractionLogTableMap::COL_PROMPT);

        $config->setUrl(sprintf(
            'table?%s',
            http_build_query($this->filterData),
        ));

        return $config;
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $query = $this->createQuery();

        $this->applyFilters($query);

        $queryResults = $this->runQuery($query, $config);

        $results = [];

        foreach ($queryResults as $row) {
            $results[] = $this->buildRow($row);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    protected function buildRow(array $row): array
    {
        $inputTokens = (int)($row[SpyAiInteractionLogTableMap::COL_INPUT_TOKENS] ?? 0);
        $outputTokens = (int)($row[SpyAiInteractionLogTableMap::COL_OUTPUT_TOKENS] ?? 0);

        $logId = (int)($row[SpyAiInteractionLogTableMap::COL_ID_AI_INTERACTION_LOG] ?? 0);

        return [
            SpyAiInteractionLogTableMap::COL_PROMPT => $this->buildPromptCell($row[SpyAiInteractionLogTableMap::COL_PROMPT] ?? '', $logId),
            SpyAiInteractionLogTableMap::COL_CONVERSATION_REFERENCE => $row[SpyAiInteractionLogTableMap::COL_CONVERSATION_REFERENCE] ?? '',
            SpyAiInteractionLogTableMap::COL_PROVIDER => $row[SpyAiInteractionLogTableMap::COL_PROVIDER] ?? '',
            SpyAiInteractionLogTableMap::COL_MODEL => $row[SpyAiInteractionLogTableMap::COL_MODEL] ?? '',
            static::COLUMN_TOTAL_TOKENS => $inputTokens + $outputTokens,
            SpyAiInteractionLogTableMap::COL_CONFIGURATION_NAME => $row[SpyAiInteractionLogTableMap::COL_CONFIGURATION_NAME] ?? '',
            SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL => $this->buildStatusBadge((bool)$row[SpyAiInteractionLogTableMap::COL_IS_SUCCESSFUL]),
            SpyAiInteractionLogTableMap::COL_INFERENCE_TIME_MS => $row[SpyAiInteractionLogTableMap::COL_INFERENCE_TIME_MS] ?? 0,
            SpyAiInteractionLogTableMap::COL_CREATED_AT => $this->formatDateTime($row[SpyAiInteractionLogTableMap::COL_CREATED_AT] ?? null),
        ];
    }

    protected function buildPromptCell(?string $prompt, int $logId = 0): string
    {
        $idMarker = sprintf('<span class="js-log-id" data-log-id="%d" style="display:none"></span>', $logId);

        if ($prompt === null || $prompt === '') {
            return $idMarker;
        }

        $truncated = mb_strlen($prompt) > static::PROMPT_TRUNCATE_LENGTH
            ? mb_substr($prompt, 0, static::PROMPT_TRUNCATE_LENGTH) . '...'
            : $prompt;

        return sprintf('%s<span title="%s">%s</span>', $idMarker, htmlspecialchars($prompt), htmlspecialchars($truncated));
    }

    protected function buildStatusBadge(bool $isSuccessful): string
    {
        $translator = $this->getTranslator();

        if ($isSuccessful) {
            $label = $translator ? $translator->trans('Success') : 'Success';

            return sprintf(static::BADGE_SUCCESS_TEMPLATE, $label);
        }

        $label = $translator ? $translator->trans('Failed') : 'Failed';

        return sprintf(static::BADGE_FAILED_TEMPLATE, $label);
    }

    protected function formatDateTime(?string $dateTime): ?string
    {
        if ($dateTime === null || $dateTime === '') {
            return null;
        }

        return (new DateTime($dateTime))->format('Y-m-d H:i:s');
    }

    protected function createQuery(): SpyAiInteractionLogQuery
    {
        return SpyAiInteractionLogQuery::create();
    }

    protected function applyFilters(SpyAiInteractionLogQuery $query): void
    {
        if (!empty($this->filterData[static::FILTER_CONFIGURATION_NAME])) {
            $query->filterByConfigurationName($this->filterData[static::FILTER_CONFIGURATION_NAME]);
        }

        if (isset($this->filterData[static::FILTER_IS_SUCCESSFUL]) && $this->filterData[static::FILTER_IS_SUCCESSFUL] !== '') {
            $query->filterByIsSuccessful($this->filterData[static::FILTER_IS_SUCCESSFUL] === '1');
        }

        if (!empty($this->filterData[static::FILTER_CONVERSATION_REFERENCE])) {
            $query->filterByConversationReference($this->filterData[static::FILTER_CONVERSATION_REFERENCE]);
        }

        if (!empty($this->filterData[static::FILTER_CREATED_AT_FROM])) {
            $query->filterByCreatedAt($this->filterData[static::FILTER_CREATED_AT_FROM], Criteria::GREATER_EQUAL);
        }

        if (!empty($this->filterData[static::FILTER_CREATED_AT_TO])) {
            $query->filterByCreatedAt($this->filterData[static::FILTER_CREATED_AT_TO], Criteria::LESS_EQUAL);
        }
    }
}
