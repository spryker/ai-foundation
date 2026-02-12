<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AiFoundation\Persistence;

use Orm\Zed\AiFoundation\Persistence\SpyAiConversationHistoryQuery;
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
}
