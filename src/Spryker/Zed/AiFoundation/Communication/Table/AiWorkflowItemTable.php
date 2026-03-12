<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Communication\Table;

use DateTime;
use Orm\Zed\AiFoundation\Persistence\Map\SpyAiWorkflowItemTableMap;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

/**
 * @module AiFoundation
 * @module StateMachine
 */
class AiWorkflowItemTable extends AbstractTable
{
    protected const string COLUMN_ID_AI_WORKFLOW_ITEM = SpyAiWorkflowItemTableMap::COL_ID_AI_WORKFLOW_ITEM;

    protected const string COLUMN_PROCESS_NAME = 'process_name';

    protected const string COLUMN_STATE_NAME = 'state_name';

    protected const string COLUMN_CREATED_AT = SpyAiWorkflowItemTableMap::COL_CREATED_AT;

    protected const string COLUMN_UPDATED_AT = SpyAiWorkflowItemTableMap::COL_UPDATED_AT;

    protected const string COLUMN_ACTIONS = 'Actions';

    protected const string URL_DETAIL = '/ai-foundation/ai-workflow/detail';

    protected const string PARAM_ID = 'id';

    protected const string STATE_NOT_STARTED = 'Not started';

    protected const string PROCESS_NOT_STARTED = 'Not started';

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader([
            static::COLUMN_ID_AI_WORKFLOW_ITEM => 'ID',
            static::COLUMN_PROCESS_NAME => 'Process Name',
            static::COLUMN_STATE_NAME => 'State',
            static::COLUMN_CREATED_AT => 'Created At',
            static::COLUMN_UPDATED_AT => 'Updated At',
            static::COLUMN_ACTIONS => static::COLUMN_ACTIONS,
        ]);

        $config->setSortable([
            static::COLUMN_ID_AI_WORKFLOW_ITEM,
            static::COLUMN_PROCESS_NAME,
            static::COLUMN_STATE_NAME,
            static::COLUMN_CREATED_AT,
            static::COLUMN_UPDATED_AT,
        ]);

        $config->setDefaultSortField(static::COLUMN_CREATED_AT, TableConfiguration::SORT_DESC);
        $config->addRawColumn(static::COLUMN_ACTIONS);

        return $config;
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $query = $this->createAiWorkflowItemQuery()
            ->useStateQuery(null, Criteria::LEFT_JOIN)
                ->useProcessQuery(null, Criteria::LEFT_JOIN)
                ->endUse()
            ->endUse()
            ->withColumn('spy_state_machine_item_state.name', static::COLUMN_STATE_NAME)
            ->withColumn('spy_state_machine_process.name', static::COLUMN_PROCESS_NAME);

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
        $stateName = $row[static::COLUMN_STATE_NAME] ?? static::STATE_NOT_STARTED;
        $processName = $row[static::COLUMN_PROCESS_NAME] ?? static::PROCESS_NOT_STARTED;

        return [
            static::COLUMN_ID_AI_WORKFLOW_ITEM => $row[static::COLUMN_ID_AI_WORKFLOW_ITEM],
            static::COLUMN_PROCESS_NAME => $processName,
            static::COLUMN_STATE_NAME => $stateName,
            static::COLUMN_CREATED_AT => $this->formatDateTime($row[static::COLUMN_CREATED_AT]),
            static::COLUMN_UPDATED_AT => $this->formatDateTime($row[static::COLUMN_UPDATED_AT]),
            static::COLUMN_ACTIONS => $this->buildActions($row),
        ];
    }

    protected function formatDateTime(?string $dateTime): ?string
    {
        if (!$dateTime) {
            return null;
        }

        return (new DateTime($dateTime))->format('Y-m-d H:i:s');
    }

    /**
     * @param array<string, mixed> $row
     */
    protected function buildActions(array $row): string
    {
        return $this->generateViewButton(
            Url::generate(static::URL_DETAIL, [static::PARAM_ID => $row[static::COLUMN_ID_AI_WORKFLOW_ITEM]]),
            'View',
        );
    }

    protected function createAiWorkflowItemQuery(): SpyAiWorkflowItemQuery
    {
        return SpyAiWorkflowItemQuery::create();
    }
}
