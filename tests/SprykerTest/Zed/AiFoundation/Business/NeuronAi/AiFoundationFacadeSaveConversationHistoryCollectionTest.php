<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConversationHistoryCollectionRequestTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryTransfer;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group NeuronAi
 * @group Facade
 * @group AiFoundationFacadeSaveConversationHistoryCollectionTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeSaveConversationHistoryCollectionTest extends Unit
{
    protected const string MESSAGES_JSON = '[{"role":"user","content":"Hello"}]';

    protected const string OTHER_MESSAGES_JSON = '[{"role":"user","content":"Takeover"}]';

    protected const string CONVERSATION_REFERENCE_PREFIX = 'test-save-conversation-history';

    protected AiFoundationBusinessTester $tester;

    public function testGivenValidRequestWhenSavingConversationHistoryThenHistoryIsPersisted(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            $this->createConversationHistoryCollectionRequest($conversationReference, static::MESSAGES_JSON),
        );

        // Assert
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());

        $conversationHistoryEntity = $this->tester->findConversationHistoryEntity($conversationReference);
        $this->assertNotNull($conversationHistoryEntity);
        $this->assertSame($conversationReference, $conversationHistoryEntity->getConversationReference());
        $this->assertSame(static::MESSAGES_JSON, $conversationHistoryEntity->getOriginalMessages());
        $this->assertCount(1, $conversationHistoryCollectionResponseTransfer->getConversationHistories());
        $this->assertSame($conversationReference, $conversationHistoryCollectionResponseTransfer->getConversationHistories()->offsetGet(0)->getConversationReference());
    }

    public function testGivenAPreexistingRowWhenSavingConversationHistoryThenTheStoredMessagesAreOverwritten(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($conversationReference);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            $this->createConversationHistoryCollectionRequest($conversationReference, static::OTHER_MESSAGES_JSON),
        );

        // Assert
        $this->assertInstanceOf(ConversationHistoryCollectionResponseTransfer::class, $conversationHistoryCollectionResponseTransfer);
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());

        $conversationHistoryEntity = $this->tester->findConversationHistoryEntity($conversationReference);

        $this->assertNotNull($conversationHistoryEntity);
        $this->assertSame(static::OTHER_MESSAGES_JSON, $conversationHistoryEntity->getOriginalMessages());
    }

    public function testGivenMissingConversationReferenceWhenSavingConversationHistoryThenWriteIsRejected(): void
    {
        // Arrange
        $conversationHistoryCollectionRequestTransfer = (new ConversationHistoryCollectionRequestTransfer())
            ->addConversationHistory((new ConversationHistoryTransfer())->setOriginalMessages(static::MESSAGES_JSON));

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection($conversationHistoryCollectionRequestTransfer);

        // Assert
        $this->assertFalse($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertGreaterThan(0, $conversationHistoryCollectionResponseTransfer->getErrors()->count());
    }

    public function testGivenExistingConversationWhenSavingAgainThenTheSameRowIsUpdated(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->getFacade()->saveConversationHistoryCollection(
            $this->createConversationHistoryCollectionRequest($conversationReference, static::MESSAGES_JSON),
        );

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            $this->createConversationHistoryCollectionRequest($conversationReference, static::OTHER_MESSAGES_JSON),
        );

        // Assert
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertSame(1, $this->tester->countConversationHistoryEntities($conversationReference));

        $conversationHistoryEntity = $this->tester->findConversationHistoryEntity($conversationReference);

        $this->assertNotNull($conversationHistoryEntity);
        $this->assertSame(static::OTHER_MESSAGES_JSON, $conversationHistoryEntity->getOriginalMessages());
    }

    public function testGivenNoConversationHistoriesWhenSavingThenWriteIsRejected(): void
    {
        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            new ConversationHistoryCollectionRequestTransfer(),
        );

        // Assert
        $this->assertFalse($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $conversationHistoryCollectionResponseTransfer->getErrors());
    }

    public function testGivenOneItemWithoutConversationReferenceWhenSavingThenNothingIsPersisted(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $conversationHistoryCollectionRequestTransfer = $this->createConversationHistoryCollectionRequest($conversationReference, static::MESSAGES_JSON)
            ->addConversationHistory((new ConversationHistoryTransfer())->setOriginalMessages(static::OTHER_MESSAGES_JSON));

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            $conversationHistoryCollectionRequestTransfer,
        );

        // Assert
        $this->assertFalse($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertNull($this->tester->findConversationHistoryEntity($conversationReference));
    }

    public function testGivenSeveralConversationHistoriesWhenSavingThenEachIsPersisted(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $otherConversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $conversationHistoryCollectionRequestTransfer = $this->createConversationHistoryCollectionRequest($conversationReference, static::MESSAGES_JSON)
            ->addConversationHistory(
                (new ConversationHistoryTransfer())
                    ->setConversationReference($otherConversationReference)
                    ->setOriginalMessages(static::OTHER_MESSAGES_JSON),
            );

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->saveConversationHistoryCollection(
            $conversationHistoryCollectionRequestTransfer,
        );

        // Assert
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertCount(2, $conversationHistoryCollectionResponseTransfer->getConversationHistories());
        $this->assertSame(static::MESSAGES_JSON, $this->tester->findConversationHistoryEntity($conversationReference)?->getOriginalMessages());
        $this->assertSame(static::OTHER_MESSAGES_JSON, $this->tester->findConversationHistoryEntity($otherConversationReference)?->getOriginalMessages());
    }

    protected function createConversationHistoryCollectionRequest(
        string $conversationReference,
        string $originalMessages
    ): ConversationHistoryCollectionRequestTransfer {
        return (new ConversationHistoryCollectionRequestTransfer())
            ->addConversationHistory(
                (new ConversationHistoryTransfer())
                    ->setConversationReference($conversationReference)
                    ->setOriginalMessages($originalMessages),
            );
    }
}
