<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConversationHistoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\ConversationHistoryCollectionResponseTransfer;
use Generated\Shared\Transfer\ConversationHistoryConditionsTransfer;
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
 * @group AiFoundationFacadeDeleteConversationHistoryCollectionTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeDeleteConversationHistoryCollectionTest extends Unit
{
    protected const string BLANK_CONVERSATION_REFERENCE = '   ';

    protected const string CONVERSATION_REFERENCE_PREFIX = 'test-delete-conversation-history';

    protected AiFoundationBusinessTester $tester;

    public function testGivenAPersistedConversationWhenDeletingConversationHistoryThenTheRowIsRemoved(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($conversationReference);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->deleteConversationHistoryCollection(
            (new ConversationHistoryCollectionDeleteCriteriaTransfer())->setConversationHistoryConditions((new ConversationHistoryConditionsTransfer())->addConversationReference($conversationReference)),
        );

        // Assert
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertSame(0, $conversationHistoryCollectionResponseTransfer->getErrors()->count());
        $this->assertSame(0, $this->tester->countConversationHistoryEntities($conversationReference));
    }

    public function testGivenSeveralPersistedConversationsWhenDeletingOneThenTheOthersSurvive(): void
    {
        // Arrange
        $deletedConversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $keptConversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($deletedConversationReference);
        $this->tester->haveConversationHistory($keptConversationReference);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->deleteConversationHistoryCollection(
            (new ConversationHistoryCollectionDeleteCriteriaTransfer())->setConversationHistoryConditions((new ConversationHistoryConditionsTransfer())->addConversationReference($deletedConversationReference)),
        );

        // Assert
        $this->assertTrue($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertSame(0, $this->tester->countConversationHistoryEntities($deletedConversationReference));
        $this->assertSame(1, $this->tester->countConversationHistoryEntities($keptConversationReference));
    }

    public function testGivenAnEmptyReferenceListWhenDeletingConversationHistoryThenNothingIsDeleted(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($conversationReference);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->deleteConversationHistoryCollection(new ConversationHistoryCollectionDeleteCriteriaTransfer());

        // Assert
        $this->assertInstanceOf(ConversationHistoryCollectionResponseTransfer::class, $conversationHistoryCollectionResponseTransfer);
        $this->assertFalse($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertGreaterThan(0, $conversationHistoryCollectionResponseTransfer->getErrors()->count());
        $this->assertSame(1, $this->tester->countConversationHistoryEntities($conversationReference));
    }

    public function testGivenABlankReferenceWhenDeletingConversationHistoryThenNothingIsDeleted(): void
    {
        // Arrange
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($conversationReference);

        // Act
        $conversationHistoryCollectionResponseTransfer = $this->tester->getFacade()->deleteConversationHistoryCollection(
            (new ConversationHistoryCollectionDeleteCriteriaTransfer())->setConversationHistoryConditions((new ConversationHistoryConditionsTransfer())->addConversationReference(static::BLANK_CONVERSATION_REFERENCE)),
        );

        // Assert
        $this->assertFalse($conversationHistoryCollectionResponseTransfer->getIsSuccessful());
        $this->assertGreaterThan(0, $conversationHistoryCollectionResponseTransfer->getErrors()->count());
        $this->assertSame(1, $this->tester->countConversationHistoryEntities($conversationReference));
    }
}
