<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Persistence;

use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiInteractionLogQuery;
use Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiInteractionLogMapper;
use Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiInteractionLogMapperInterface;
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
    /**
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery
     */
    public function createConversationHistoryQuery(): SpyAiConversationHistoryQuery
    {
        return SpyAiConversationHistoryQuery::create();
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\ConversationHistoryMapper
     */
    public function createConversationHistoryMapper(): ConversationHistoryMapper
    {
        return new ConversationHistoryMapper();
    }

    /**
     * @return \Spryker\Zed\AiFoundation\Persistence\Propel\Mapper\AiWorkflowItemMapperInterface
     */
    public function createAiWorkflowItemMapper(): AiWorkflowItemMapperInterface
    {
        return new AiWorkflowItemMapper();
    }

    /**
     * @return \Orm\Zed\AiFoundation\Persistence\SpyAiWorkflowItemQuery
     */
    public function createAiWorkflowItemQuery(): SpyAiWorkflowItemQuery
    {
        return SpyAiWorkflowItemQuery::create();
    }

    public function createAiInteractionLogMapper(): AiInteractionLogMapperInterface
    {
        return new AiInteractionLogMapper();
    }

    public function createAiInteractionLogQuery(): SpyAiInteractionLogQuery
    {
        return SpyAiInteractionLogQuery::create();
    }
}
