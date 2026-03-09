<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Business\AiInteractionLog\Creator;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionResponseTransfer;
use Spryker\Zed\AiFoundation\Persistence\AiFoundationEntityManagerInterface;

class AiInteractionLogCreator implements AiInteractionLogCreatorInterface
{
    public function __construct(protected AiFoundationEntityManagerInterface $entityManager)
    {
    }

    public function createAiInteractionLogCollection(
        AiInteractionLogCollectionRequestTransfer $aiInteractionLogCollectionRequestTransfer,
    ): AiInteractionLogCollectionResponseTransfer {
        return $this->entityManager->createAiInteractionLogCollection($aiInteractionLogCollectionRequestTransfer);
    }
}
