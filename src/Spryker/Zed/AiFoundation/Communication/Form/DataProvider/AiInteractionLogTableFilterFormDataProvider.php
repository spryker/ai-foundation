<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Form\DataProvider;

use DateTime;
use Orm\Zed\AiFoundation\Persistence\Map\SpyAiInteractionLogTableMap;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Communication\Form\AiInteractionLogTableFilterForm;

class AiInteractionLogTableFilterFormDataProvider
{
    public function __construct(protected AiFoundationConfig $config)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $days = $this->config->getAiInteractionLogDefaultDateRangeDays();

        return [
            AiInteractionLogTableFilterForm::FIELD_CREATED_AT_FROM => new DateTime(sprintf('-%d days', $days)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            AiInteractionLogTableFilterForm::OPTION_CONFIGURATION_NAME_CHOICES => $this->getDistinctConfigurationNames(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getDistinctConfigurationNames(): array
    {
        $configurationNames = SpyAiInteractionLogQuery::create()
            ->select([SpyAiInteractionLogTableMap::COL_CONFIGURATION_NAME])
            ->setDistinct()
            ->orderByConfigurationName()
            ->find()
            ->getData();

        $choices = [];

        foreach ($configurationNames as $name) {
            if ($name === null || $name === '') {
                continue;
            }

            $choices[$name] = $name;
        }

        return $choices;
    }
}
