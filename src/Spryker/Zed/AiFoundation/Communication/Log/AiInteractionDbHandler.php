<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Log;

use Generated\Shared\Transfer\AiInteractionLogCollectionRequestTransfer;
use Generated\Shared\Transfer\AiInteractionLogCollectionTransfer;
use Monolog\Handler\AbstractProcessingHandler;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;

/**
 * Monolog handler that persists AI interaction log records to the database.
 */
class AiInteractionDbHandler extends AbstractProcessingHandler
{
    public function __construct(protected AiFoundationFacadeInterface $aiFoundationFacade)
    {
        parent::__construct();
    }

    /**
     * @param array<string, mixed> $record
     */
    protected function write(array $record): void
    {
        $context = $record['context'] ?? [];

        /** @var \Generated\Shared\Transfer\AiInteractionLogTransfer|null $aiInteractionLogTransfer */
        $aiInteractionLogTransfer = $context[AiFoundationConstants::AUDIT_LOG_CONTEXT_KEY_TRANSFER] ?? null;

        if ($aiInteractionLogTransfer === null) {
            return;
        }

        $this->aiFoundationFacade->createAiInteractionLogCollection(
            (new AiInteractionLogCollectionRequestTransfer())
                ->setAiInteractionLogCollection(
                    (new AiInteractionLogCollectionTransfer())
                        ->addAiInteractionLog($aiInteractionLogTransfer),
                ),
        );
    }
}
