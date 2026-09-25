<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ChatSessionRequestTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
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
 * @group AiFoundationFacadeInitChatSessionTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeInitChatSessionTest extends Unit
{
    protected const string ALLOWLISTED_CONFIGURATION_NAME = 'test_allowlisted_configuration';

    protected const string NON_ALLOWLISTED_CONFIGURATION_NAME = 'test_non_allowlisted_configuration';

    protected const string PROVIDER_MODEL = 'test-model';

    protected const string PROVIDER_KEY = 'test-key';

    protected const int CONTEXT_WINDOW = 50000;

    protected const string CONVERSATION_REFERENCE_PREFIX = 'test-init-chat-session';

    protected AiFoundationBusinessTester $tester;

    public function testGivenAllowlistedConfigurationWhenInitializingChatSessionThenConfigurationAndHistoryAreReturnedTogether(): void
    {
        // Arrange
        $this->mockAiFoundationConfig([static::ALLOWLISTED_CONFIGURATION_NAME]);
        $conversationReference = $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX);
        $this->tester->haveConversationHistory($conversationReference);

        // Act
        $chatSessionResponseTransfer = $this->tester->getFacade()->initChatSession(
            $this->createChatSessionRequest(static::ALLOWLISTED_CONFIGURATION_NAME, $conversationReference),
        );

        // Assert
        $this->assertTrue($chatSessionResponseTransfer->getIsSuccessful());
        $this->assertNotNull($chatSessionResponseTransfer->getAiConfiguration());
        $this->assertSame(static::ALLOWLISTED_CONFIGURATION_NAME, $chatSessionResponseTransfer->getAiConfiguration()->getName());
        $this->assertSame(AiFoundationConstants::PROVIDER_OPENAI, $chatSessionResponseTransfer->getAiConfiguration()->getProviderName());
        $this->assertNotNull($chatSessionResponseTransfer->getConversationHistory());
        $this->assertSame($conversationReference, $chatSessionResponseTransfer->getConversationHistory()->getConversationReference());
    }

    public function testGivenNonAllowlistedConfigurationWhenInitializingChatSessionThenResponseIsUnsuccessfulAndNotNull(): void
    {
        // Arrange
        $this->mockAiFoundationConfig([static::ALLOWLISTED_CONFIGURATION_NAME]);
        $chatSessionRequestTransfer = $this->createChatSessionRequest(
            static::NON_ALLOWLISTED_CONFIGURATION_NAME,
            $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX),
        );

        // Act
        $chatSessionResponseTransfer = $this->tester->getFacade()->initChatSession($chatSessionRequestTransfer);

        // Assert
        $this->assertInstanceOf(ChatSessionResponseTransfer::class, $chatSessionResponseTransfer);
        $this->assertFalse($chatSessionResponseTransfer->getIsSuccessful());
        $this->assertNull($chatSessionResponseTransfer->getAiConfiguration());
        $this->assertGreaterThan(0, $chatSessionResponseTransfer->getErrors()->count());
    }

    public function testGivenEmptyAllowlistWhenInitializingChatSessionThenEveryConfigurationIsRejected(): void
    {
        // Arrange
        $this->mockAiFoundationConfig([]);
        $chatSessionRequestTransfer = $this->createChatSessionRequest(
            static::ALLOWLISTED_CONFIGURATION_NAME,
            $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX),
        );

        // Act
        $chatSessionResponseTransfer = $this->tester->getFacade()->initChatSession($chatSessionRequestTransfer);

        // Assert
        $this->assertFalse($chatSessionResponseTransfer->getIsSuccessful());
        $this->assertNull($chatSessionResponseTransfer->getAiConfiguration());
    }

    public function testGivenNoStoredHistoryWhenInitializingChatSessionThenHistoryIsEmpty(): void
    {
        // Arrange
        $this->mockAiFoundationConfig([static::ALLOWLISTED_CONFIGURATION_NAME]);
        $chatSessionRequestTransfer = $this->createChatSessionRequest(
            static::ALLOWLISTED_CONFIGURATION_NAME,
            $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX),
        );

        // Act
        $chatSessionResponseTransfer = $this->tester->getFacade()->initChatSession($chatSessionRequestTransfer);

        // Assert
        $this->assertTrue($chatSessionResponseTransfer->getIsSuccessful());
        $this->assertNull($chatSessionResponseTransfer->getConversationHistory());
    }

    public function testGivenAllowlistedConfigurationWhenInitializingChatSessionThenConfiguredContextWindowIsReturned(): void
    {
        // Arrange
        $this->mockAiFoundationConfig([static::ALLOWLISTED_CONFIGURATION_NAME]);
        $chatSessionRequestTransfer = $this->createChatSessionRequest(
            static::ALLOWLISTED_CONFIGURATION_NAME,
            $this->tester->generateUniqueConversationReference(static::CONVERSATION_REFERENCE_PREFIX),
        );

        // Act
        $chatSessionResponseTransfer = $this->tester->getFacade()->initChatSession($chatSessionRequestTransfer);

        // Assert
        $this->assertSame(static::CONTEXT_WINDOW, $chatSessionResponseTransfer->getContextWindow());
    }

    public function testGivenDefaultConfigWhenReadingClientResolvableAiConfigurationNamesThenListIsEmpty(): void
    {
        // Arrange
        $aiFoundationConfig = new AiFoundationConfig();

        // Act
        $clientResolvableAiConfigurationNames = $aiFoundationConfig->getClientResolvableAiConfigurationNames();

        // Assert
        $this->assertSame([], $clientResolvableAiConfigurationNames);
    }

    /**
     * @param array<string> $clientResolvableAiConfigurationNames
     */
    protected function mockAiFoundationConfig(array $clientResolvableAiConfigurationNames): void
    {
        $this->tester->mockConfigMethod('getClientResolvableAiConfigurationNames', $clientResolvableAiConfigurationNames);
        $this->tester->mockConfigMethod('getConversationHistoryContextWindow', static::CONTEXT_WINDOW);
        $this->tester->mockConfigMethod('getAiConfigurations', [
            static::ALLOWLISTED_CONFIGURATION_NAME => [
                AiFoundationConstants::AI_PROVIDER_NAME => AiFoundationConstants::PROVIDER_OPENAI,
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => static::PROVIDER_KEY,
                    'model' => static::PROVIDER_MODEL,
                ],
            ],
            static::NON_ALLOWLISTED_CONFIGURATION_NAME => [
                AiFoundationConstants::AI_PROVIDER_NAME => AiFoundationConstants::PROVIDER_OPENAI,
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => static::PROVIDER_KEY,
                    'model' => static::PROVIDER_MODEL,
                ],
            ],
        ]);
    }

    protected function createChatSessionRequest(
        string $aiConfigurationName,
        string $conversationReference
    ): ChatSessionRequestTransfer {
        return (new ChatSessionRequestTransfer())
            ->setAiConfigurationName($aiConfigurationName)
            ->setConversationReference($conversationReference);
    }
}
