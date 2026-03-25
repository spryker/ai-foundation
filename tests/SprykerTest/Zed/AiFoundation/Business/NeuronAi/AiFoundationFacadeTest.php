<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Attachments\Attachment;
use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Providers\AIProviderInterface;
use ReflectionProperty;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
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
 * @group AiFoundationFacadeTest
 * Add your own group annotations below this line
 */
class AiFoundationFacadeTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_ollama';

    protected const string TEST_OLLAMA_URL = 'http://localhost:11434/api';

    protected const string TEST_OLLAMA_MODEL = 'llama3.2';

    protected const string TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    protected const string TEST_USER_MESSAGE = 'Hello, AI!';

    protected const string TEST_ASSISTANT_RESPONSE = 'Hello! How can I help you today?';

    protected AiFoundationBusinessTester $tester;

    public function testPromptWithOllamaProviderCreatesProviderWithCorrectConfigAndCallsChat(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockAiProvider();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
    }

    public function testPromptCallsSystemPromptWithConfiguredValue(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testPromptCallsChatWithMappedUserMessage(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $mockProvider->expects($this->once())
            ->method('chat')
            ->with($this->callback(function (array $messages): bool {
                $this->assertCount(1, $messages);
                $this->assertInstanceOf(Message::class, $messages[0]);
                $this->assertSame(static::TEST_USER_MESSAGE, $messages[0]->getContent());

                return true;
            }))
            ->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testProviderResolverIsCalledWithCorrectProviderNameAndConfig(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $expectedProviderConfig = [
            'url' => static::TEST_OLLAMA_URL,
            'model' => static::TEST_OLLAMA_MODEL,
            'parameters' => [],
        ];

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('chat')->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->expects($this->once())
            ->method('resolve')
            ->with(
                AiFoundationConstants::PROVIDER_OLLAMA,
                $expectedProviderConfig,
            )
            ->willReturn($mockProvider);

        $facade = $this->createFacadeWithMockedProviderResolver($mockProviderResolver);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    /**
     * @dataProvider providerResolvingDataProvider
     *
     * @param array<string, mixed> $providerConfig
     */
    public function testProviderIsResolvedCorrectly(string $providerName, array $providerConfig): void
    {
        // Arrange
        $aiEngineName = 'test_' . $providerName;
        $promptRequestTransfer = $this->createPromptRequestTransferWithEngine($aiEngineName);

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('chat')->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->expects($this->once())
            ->method('resolve')
            ->with($providerName, $providerConfig)
            ->willReturn($mockProvider);

        $facade = $this->createFacadeWithMockedProviderResolverAndConfig(
            $mockProviderResolver,
            $aiEngineName,
            $providerName,
            $providerConfig,
        );

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function providerResolvingDataProvider(): array
    {
        return [
            'OpenAI provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_OPENAI,
                'providerConfig' => [
                    'key' => 'sk-test-key',
                    'model' => 'gpt-4o',
                    'parameters' => [],
                ],
            ],
            'Anthropic provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_ANTHROPIC,
                'providerConfig' => [
                    'key' => 'sk-ant-test-key',
                    'model' => 'claude-sonnet-4-20250514',
                    'version' => '2023-06-01',
                    'max_tokens' => 8192,
                    'parameters' => [],
                ],
            ],
            'Bedrock provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_BEDROCK,
                'providerConfig' => [
                    'model' => 'eu.anthropic.claude-sonnet-4-20250514-v1:0',
                    'bedrockRuntimeClient' => [
                        'region' => 'eu-west-1',
                        'version' => 'latest',
                    ],
                ],
            ],
            'Deepseek provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_DEEPSEEK,
                'providerConfig' => [
                    'key' => 'sk-deepseek-test-key',
                    'model' => 'deepseek-chat',
                    'parameters' => [],
                ],
            ],
            'Gemini provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_GEMINI,
                'providerConfig' => [
                    'key' => 'gemini-test-key',
                    'model' => 'gemini-2.0-flash',
                    'parameters' => [],
                ],
            ],
            'HuggingFace provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_HUGGINGFACE,
                'providerConfig' => [
                    'key' => 'hf-test-key',
                    'model' => 'meta-llama/Llama-3.3-70B-Instruct',
                    'parameters' => [],
                ],
            ],
            'Mistral provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_MISTRAL,
                'providerConfig' => [
                    'key' => 'mistral-test-key',
                    'model' => 'mistral-large-latest',
                    'parameters' => [],
                ],
            ],
            'Ollama provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_OLLAMA,
                'providerConfig' => [
                    'url' => 'http://localhost:11434/api',
                    'model' => 'llama3.2',
                    'parameters' => [],
                ],
            ],
            'Grok provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_GROK,
                'providerConfig' => [
                    'key' => 'xai-test-key',
                    'model' => 'grok-2-latest',
                    'parameters' => [],
                ],
            ],
            'Azure OpenAI provider' => [
                'providerName' => AiFoundationConstants::PROVIDER_AZURE_OPEN_AI,
                'providerConfig' => [
                    'key' => 'azure-test-key',
                    'endpoint' => 'https://test-resource.openai.azure.com',
                    'model' => 'test-deployment',
                    'version' => '2024-02-01',
                    'parameters' => [],
                ],
            ],
        ];
    }

    public function testGivenPromptMessageHasSingleDocumentAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithDocumentAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenPromptMessageHasSingleImageAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithImageAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::IMAGE, AttachmentContentType::BASE64);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenPromptMessageHasMultipleAttachmentsWhenPromptingThenAllAttachmentsAreMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithMultipleAttachments();
        $mockProvider = $this->createMockProviderExpectingMultipleAttachments();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenPromptMessageHasAttachmentWithMediaTypeWhenPromptingThenMediaTypeIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithAttachmentWithMediaType();
        $mockProvider = $this->createMockProviderExpectingAttachmentWithMediaType();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenProviderReturnsMessageWithAttachmentsWhenPromptingThenAttachmentsAreMappedToResponse(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningAttachments();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertCount(1, $promptResponseTransfer->getMessage()->getAttachments());
        $this->assertAttachmentMatchesExpectedValues($promptResponseTransfer->getMessage()->getAttachments()->offsetGet(0));
    }

    public function testGivenAttachmentHasUnknownTypeWhenMappingThenDefaultsToDocumentType(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithUnknownAttachmentType();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenAttachmentHasUnknownContentTypeWhenMappingThenDefaultsToUrlContentType(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithUnknownContentType();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testPromptReturnsMessageWithUsageWhenProviderIncludesTokenCounts(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $usage = new Usage(inputTokens: 100, outputTokens: 50);
        $mockMessage = $this->createMock(AssistantMessage::class);
        $mockMessage->method('getContent')->willReturn(static::TEST_ASSISTANT_RESPONSE);
        $mockMessage->method('getUsage')->willReturn($usage);
        $mockMessage->method('getAttachments')->willReturn([]);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($mockMessage);

        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
        $this->assertNotNull($promptResponseTransfer->getMessage()->getUsage());
        $this->assertSame(100, $promptResponseTransfer->getMessage()->getUsage()->getInputTokens());
        $this->assertSame(50, $promptResponseTransfer->getMessage()->getUsage()->getOutputTokens());
    }

    public function testGivenProviderReturnsMessageWithoutUsageWhenPromptingThenUsageIsNull(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningNoUsage();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertNull($promptResponseTransfer->getMessage()->getUsage());
    }

    public function testGivenProviderReturnsMessageWithZeroTokenUsageWhenPromptingThenUsageIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningZeroTokenUsage();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertNotNull($promptResponseTransfer->getMessage()->getUsage());
        $this->assertSame(0, $promptResponseTransfer->getMessage()->getUsage()->getInputTokens());
        $this->assertSame(0, $promptResponseTransfer->getMessage()->getUsage()->getOutputTokens());
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestTransferWithEngine(string $aiEngineName): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName($aiEngineName)
            ->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithDocumentAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = $this->createDocumentAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithImageAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = $this->createImageAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithMultipleAttachments(): PromptRequestTransfer
    {
        $documentAttachment = $this->createDocumentAttachment();
        $imageAttachment = $this->createImageAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($documentAttachment)->addAttachment($imageAttachment);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithAttachmentWithMediaType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE)->setContent('https://example.com/image.png')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL)->setMediaType('image/png');
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithUnknownAttachmentType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType('unknown_type')->setContent('https://example.com/document.pdf')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL);
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithUnknownContentType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)->setContent('https://example.com/document.pdf')->setContentType('unknown_content_type');
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    protected function createDocumentAttachment(): AttachmentTransfer
    {
        return (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)->setContent('https://example.com/document.pdf')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL);
    }

    protected function createImageAttachment(): AttachmentTransfer
    {
        return (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE)->setContent('base64EncodedImageData')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64);
    }

    protected function createMockAiProvider(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);

        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockAiProviderWithUsage(int $inputTokens, int $outputTokens): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);

        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $usage = new Usage(inputTokens: $inputTokens, outputTokens: $outputTokens);
        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);
        $this->setMessageUsage($assistantMessage, $usage);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderExpectingAttachment(AttachmentType $expectedType, AttachmentContentType $expectedContentType): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (array $messages) use ($expectedType, $expectedContentType): bool {
            $attachments = $messages[0]->getAttachments();
            $this->assertCount(1, $attachments);
            $this->assertSame($expectedType, $attachments[0]->type);
            $this->assertSame($expectedContentType, $attachments[0]->contentType);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderExpectingMultipleAttachments(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (array $messages): bool {
            $attachments = $messages[0]->getAttachments();
            $this->assertCount(2, $attachments);
            $this->assertSame(AttachmentType::DOCUMENT, $attachments[0]->type);
            $this->assertSame(AttachmentType::IMAGE, $attachments[1]->type);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderExpectingAttachmentWithMediaType(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (array $messages): bool {
            $attachments = $messages[0]->getAttachments();
            $this->assertCount(1, $attachments);
            $this->assertSame('image/png', $attachments[0]->mediaType);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderReturningAttachments(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $attachment = new Attachment(type: AttachmentType::IMAGE, content: 'https://example.com/result.png', contentType: AttachmentContentType::URL, mediaType: 'image/png');
        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);
        $assistantMessage->addAttachment($attachment);
        $mockProvider->method('chat')->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningUsage(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);

        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $usage = new Usage(inputTokens: 100, outputTokens: 50);
        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);
        $this->setMessageUsage($assistantMessage, $usage);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningNoUsage(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);

        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningZeroTokenUsage(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);

        $mockProvider->expects($this->once())
            ->method('systemPrompt')
            ->with(static::TEST_SYSTEM_PROMPT)
            ->willReturnSelf();

        $usage = new Usage(inputTokens: 0, outputTokens: 0);
        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);
        $this->setMessageUsage($assistantMessage, $usage);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function setMessageUsage(Message $message, Usage $usage): void
    {
        $reflectionProperty = new ReflectionProperty($message, 'usage');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $usage);
    }

    protected function assertAttachmentMatchesExpectedValues(AttachmentTransfer $attachmentTransfer): void
    {
        $this->assertSame(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE, $attachmentTransfer->getType());
        $this->assertSame('https://example.com/result.png', $attachmentTransfer->getContent());
        $this->assertSame(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL, $attachmentTransfer->getContentType());
        $this->assertSame('image/png', $attachmentTransfer->getMediaType());
    }

    protected function createFacadeWithMockedProvider(AIProviderInterface $mockProvider): AiFoundationFacadeInterface
    {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        return $this->createFacadeWithMockedProviderResolver($mockProviderResolver);
    }

    protected function createFacadeWithMockedProviderResolver(ProviderResolverInterface $mockProviderResolver): AiFoundationFacadeInterface
    {
        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->createNeuronAiMessageMapper(),
            toolMapper: $this->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createMockChatHistoryResolver(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: [],
            postPromptPlugins: [],
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $mockVendorProviderPlugin,
        );

        return $this->tester->getFacade();
    }

    protected function createFacadeWithMockedProviderResolverAndConfig(
        ProviderResolverInterface $mockProviderResolver,
        string $aiEngineName,
        string $providerName,
        array $providerConfig,
    ): AiFoundationFacadeInterface {
        $config = $this->createMock(AiFoundationConfig::class);
        $config->method('getAiConfigurations')->willReturn([
            $aiEngineName => [
                'provider_name' => $providerName,
                'provider_config' => $providerConfig,
                'system_prompt' => static::TEST_SYSTEM_PROMPT,
            ],
        ]);

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->createNeuronAiMessageMapper(),
            toolMapper: $this->createNeuronAiToolMapper(),
            chatHistoryResolver: $this->createMockChatHistoryResolver(),
            aiConfigurations: $config->getAiConfigurations(),
            aiToolSetPlugins: [],
            postPromptPlugins: [],
        );

        $mockVendorProviderPlugin = $this->createMock(VendorProviderPluginInterface::class);
        $mockVendorProviderPlugin->method('getVendorAdapter')->willReturn($neuronAiAdapter);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $mockVendorProviderPlugin,
        );

        return $this->tester->getFacade();
    }

    protected function createMockConfig(): AiFoundationConfig
    {
        $config = $this->createMock(AiFoundationConfig::class);

        $config->method('getAiConfigurations')
            ->willReturn([
                static::TEST_AI_ENGINE => [
                    'provider_name' => AiFoundationConstants::PROVIDER_OLLAMA,
                    'provider_config' => [
                        'url' => static::TEST_OLLAMA_URL,
                        'model' => static::TEST_OLLAMA_MODEL,
                        'parameters' => [],
                    ],
                    'system_prompt' => static::TEST_SYSTEM_PROMPT,
                ],
            ]);

        return $config;
    }

    protected function createNeuronAiMessageMapper(): NeuronAiMessageMapper
    {
        $transferJsonSchemaMapper = $this->createTransferJsonSchemaMapper();

        return new NeuronAiMessageMapper($transferJsonSchemaMapper);
    }

    protected function createTransferJsonSchemaMapper(): TransferJsonSchemaMapperInterface
    {
        return new TransferJsonSchemaMapper();
    }

    public function createNeuronAiToolMapper(): NeuronAiToolMapperInterface
    {
        return new NeuronAiToolMapper();
    }

    protected function createMockChatHistoryResolver(): ChatHistoryResolverInterface
    {
        $mockChatHistoryResolver = $this->createMock(ChatHistoryResolverInterface::class);
        $mockChatHistoryResolver->method('resolve')->willReturn(null);

        return $mockChatHistoryResolver;
    }
}
