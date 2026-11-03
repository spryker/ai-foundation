<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Zed\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
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
 * @group AiFoundationFacadeConversationHistoryReasoningTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeConversationHistoryReasoningTest extends Unit
{
    protected const string AI_CONFIGURATION_ANTHROPIC = 'test_anthropic';

    protected const string AI_CONFIGURATION_ANTHROPIC_OTHER_MODEL = 'test_anthropic_other_model';

    protected const string AI_CONFIGURATION_OPENAI = 'test_openai';

    protected const string MODEL_NAME = 'claude-test-model';

    protected const string MODEL_NAME_OTHER = 'claude-other-test-model';

    protected const string MODEL_NAME_OPENAI = 'gpt-test-model';

    protected const string PROVIDER_KEY = 'test-key';

    protected const string CONVERSATION_PREFIX = 'test-chat-history-reasoning-';

    protected const string FIRST_PROMPT = 'First question';

    protected const string SECOND_PROMPT = 'Second question';

    protected const string ANSWER = 'Test answer';

    protected const string REASONING_CONTENT = 'Thinking about the answer';

    protected const string REASONING_SIGNATURE = 'reasoning-signature';

    protected const string REDACTED_THINKING_DATA = 'redacted-thinking-data';

    protected const string METADATA_KEY_ANTHROPIC_REDACTED_THINKING = 'anthropic_redacted_thinking';

    protected const string METADATA_KEY_PROVIDER_NAME = 'ai_provider_name';

    protected const string METADATA_KEY_MODEL_NAME = 'ai_model_name';

    protected const string CONFIG_KEY_PROVIDER_NAME = 'provider_name';

    protected const string CONFIG_KEY_PROVIDER_CONFIG = 'provider_config';

    protected const string PROVIDER_CONFIG_KEY_KEY = 'key';

    protected const string PROVIDER_CONFIG_KEY_MODEL = 'model';

    protected const string PROVIDER_CONFIG_KEY_PARAMETERS = 'parameters';

    protected const int PREVIOUS_ASSISTANT_MESSAGE_INDEX = 1;

    protected AiFoundationBusinessTester $tester;

    /**
     * @var array<int, array<\NeuronAI\Chat\Messages\Message>>
     */
    protected array $providerCalls = [];

    /**
     * @dataProvider provideForeignAiConfigurations
     */
    public function testGivenAnswerOfAnotherOriginWhenPromptingThenHistorySentToProviderHasNoReasoning(string $aiConfigurationName): void
    {
        // Arrange
        $facade = $this->createFacadeWithRecordingProvider();
        $conversationReference = $this->generateConversationReference();
        $facade->prompt($this->createPromptRequest($conversationReference, static::AI_CONFIGURATION_ANTHROPIC, static::FIRST_PROMPT));

        // Act
        $facade->prompt($this->createPromptRequest($conversationReference, $aiConfigurationName, static::SECOND_PROMPT));

        // Assert
        $previousAssistantMessage = $this->getPreviousAssistantMessageOfLastProviderCall();
        $this->assertNull($previousAssistantMessage->getReasoning());
        $this->assertNull($previousAssistantMessage->getMetadata(static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING));
        $this->assertSame(static::ANSWER, $previousAssistantMessage->getContent());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideForeignAiConfigurations(): array
    {
        return [
            'another provider' => [static::AI_CONFIGURATION_OPENAI],
            'same provider with another model' => [static::AI_CONFIGURATION_ANTHROPIC_OTHER_MODEL],
        ];
    }

    public function testGivenAnswerOfSameOriginWhenPromptingThenHistorySentToProviderKeepsReasoning(): void
    {
        // Arrange
        $facade = $this->createFacadeWithRecordingProvider();
        $conversationReference = $this->generateConversationReference();
        $facade->prompt($this->createPromptRequest($conversationReference, static::AI_CONFIGURATION_ANTHROPIC, static::FIRST_PROMPT));

        // Act
        $facade->prompt($this->createPromptRequest($conversationReference, static::AI_CONFIGURATION_ANTHROPIC, static::SECOND_PROMPT));

        // Assert
        $previousAssistantMessage = $this->getPreviousAssistantMessageOfLastProviderCall();
        $this->assertSame(static::REASONING_SIGNATURE, $previousAssistantMessage->getReasoning()?->id);
        $this->assertSame(
            [1 => static::REDACTED_THINKING_DATA],
            $previousAssistantMessage->getMetadata(static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING),
        );
    }

    public function testGivenAnswerWhenPromptingThenStoredAnswerIsMarkedWithProviderAndModel(): void
    {
        // Arrange
        $facade = $this->createFacadeWithRecordingProvider();
        $conversationReference = $this->generateConversationReference();
        $facade->prompt($this->createPromptRequest($conversationReference, static::AI_CONFIGURATION_ANTHROPIC, static::FIRST_PROMPT));

        // Act
        $facade->prompt($this->createPromptRequest($conversationReference, static::AI_CONFIGURATION_ANTHROPIC, static::SECOND_PROMPT));

        // Assert
        $previousAssistantMessage = $this->getPreviousAssistantMessageOfLastProviderCall();
        $this->assertSame(AiFoundationConstants::PROVIDER_ANTHROPIC, $previousAssistantMessage->getMetadata(static::METADATA_KEY_PROVIDER_NAME));
        $this->assertSame(static::MODEL_NAME, $previousAssistantMessage->getMetadata(static::METADATA_KEY_MODEL_NAME));
    }

    protected function getPreviousAssistantMessageOfLastProviderCall(): Message
    {
        $this->assertNotEmpty($this->providerCalls);

        $lastProviderCallMessages = $this->providerCalls[count($this->providerCalls) - 1];

        $this->assertArrayHasKey(static::PREVIOUS_ASSISTANT_MESSAGE_INDEX, $lastProviderCallMessages);

        return $lastProviderCallMessages[static::PREVIOUS_ASSISTANT_MESSAGE_INDEX];
    }

    protected function createFacadeWithRecordingProvider(): AiFoundationFacadeInterface
    {
        $providerResolverMock = $this->createMock(ProviderResolverInterface::class);
        $providerResolverMock->method('resolve')->willReturn($this->createRecordingProviderMock());

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $providerResolverMock,
            messageMapper: $this->tester->getFactory()->createNeuronAiMessageMapper(),
            toolMapper: $this->tester->getFactory()->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->tester->getFactory()->createNeuronAiChatHistoryResolver(),
            aiConfigurations: $this->createAiConfigurations(),
            aiToolSetPlugins: [],
            postPromptPlugins: [],
        );

        $vendorProviderPluginMock = $this->createMock(VendorProviderPluginInterface::class);
        $vendorProviderPluginMock->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER, $vendorProviderPluginMock);

        $aiFoundationFacade = $this->tester->getFacade();

        $this->assertInstanceOf(AiFoundationFacadeInterface::class, $aiFoundationFacade);

        return $aiFoundationFacade;
    }

    protected function createRecordingProviderMock(): AIProviderInterface
    {
        $providerMock = $this->createMock(AIProviderInterface::class);
        $providerMock->method('systemPrompt')->willReturnSelf();
        $providerMock->method('chat')->willReturnCallback(function (Message ...$messages): Message {
            $this->providerCalls[] = $messages;

            return $this->createAnswerWithReasoning();
        });

        return $providerMock;
    }

    protected function createAnswerWithReasoning(): AssistantMessage
    {
        $assistantMessage = new AssistantMessage([
            new ReasoningContent(static::REASONING_CONTENT, static::REASONING_SIGNATURE),
            new TextContent(static::ANSWER),
        ]);
        $assistantMessage->addMetadata(static::METADATA_KEY_ANTHROPIC_REDACTED_THINKING, [1 => static::REDACTED_THINKING_DATA]);

        return $assistantMessage;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function createAiConfigurations(): array
    {
        return [
            static::AI_CONFIGURATION_ANTHROPIC => $this->createAiConfiguration(AiFoundationConstants::PROVIDER_ANTHROPIC, static::MODEL_NAME),
            static::AI_CONFIGURATION_ANTHROPIC_OTHER_MODEL => $this->createAiConfiguration(AiFoundationConstants::PROVIDER_ANTHROPIC, static::MODEL_NAME_OTHER),
            static::AI_CONFIGURATION_OPENAI => $this->createAiConfiguration(AiFoundationConstants::PROVIDER_OPENAI, static::MODEL_NAME_OPENAI),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function createAiConfiguration(string $providerName, string $modelName): array
    {
        return [
            static::CONFIG_KEY_PROVIDER_NAME => $providerName,
            static::CONFIG_KEY_PROVIDER_CONFIG => [
                static::PROVIDER_CONFIG_KEY_KEY => static::PROVIDER_KEY,
                static::PROVIDER_CONFIG_KEY_MODEL => $modelName,
                static::PROVIDER_CONFIG_KEY_PARAMETERS => [],
            ],
        ];
    }

    protected function createPromptRequest(string $conversationReference, string $aiConfigurationName, string $content): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName($aiConfigurationName)
            ->setConversationReference($conversationReference)
            ->setPromptMessage((new PromptMessageTransfer())->setContent($content))
            ->setMaxRetries(1);
    }

    protected function generateConversationReference(): string
    {
        return static::CONVERSATION_PREFIX . uniqid('', true);
    }
}
