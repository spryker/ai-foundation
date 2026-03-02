<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiWorkflowItemMapper;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiWorkflowItemMapperInterface;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\ConversationHistoryMapper;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \Spryker\Zed\AiFoundation\AiFoundationConfig getConfig()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\AiFoundation\Persistence\AiFoundationRepositoryInterface getRepository()
 */
class AiFoundationPersistenceFactory extends AbstractPersistenceFactory
{
    public function createConversationHistoryQuery(): SpyAiConversationHistoryQuery
    {
        return SpyAiConversationHistoryQuery::create();
    }

    public function createConversationHistoryMapper(): ConversationHistoryMapper
    {
        return new ConversationHistoryMapper();
    }

    public function createAiWorkflowItemMapper(): AiWorkflowItemMapperInterface
    {
        return new AiWorkflowItemMapper();
    }

    public function createAiWorkflowItemQuery(): SpyAiWorkflowItemQuery
    {
        return SpyAiWorkflowItemQuery::create();
    }
}
