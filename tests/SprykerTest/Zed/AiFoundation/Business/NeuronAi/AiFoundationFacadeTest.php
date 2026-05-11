<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business\NeuronAi;

use Aws\Token\BedrockTokenProvider;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use InvalidArgumentException;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\FileContent;
use NeuronAI\Chat\Messages\ContentBlocks\ImageContent;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\AWS\BedrockRuntime;
use ReflectionProperty;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Zed\AiFoundation\Business\AiFoundationFacadeInterface;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapper;
use Spryker\Zed\AiFoundation\Business\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ChatHistoryResolver\ChatHistoryResolverInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Extractor\MessageContentExtractor;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Zed\AiFoundation\Business\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolver;
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

    protected const string TEST_BEDROCK_MODEL = 'eu.anthropic.claude-sonnet-4-20250514-v1:0';

    protected const string TEST_BEDROCK_REGION = 'eu-west-1';

    protected const string TEST_BEDROCK_BEARER_TOKEN = 'bedrock-api-key-test-bearer-value';

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
            ->with($this->callback(function (Message $message): bool {
                $this->assertSame(static::TEST_USER_MESSAGE, $message->getContent());

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

    public function testResolveBedrockProviderWithTokenWiresBearerAuthAndTokenProvider(): void
    {
        // Arrange
        $config = [
            'model' => static::TEST_BEDROCK_MODEL,
            'bedrockRuntimeClient' => [
                'region' => static::TEST_BEDROCK_REGION,
                'token' => static::TEST_BEDROCK_BEARER_TOKEN,
            ],
        ];

        // Act
        $provider = (new ProviderResolver())->resolve(AiFoundationConstants::PROVIDER_BEDROCK, $config);
        $client = (new ReflectionProperty(BedrockRuntime::class, 'bedrockRuntimeClient'))->getValue($provider);

        // Assert
        $this->assertSame([BedrockTokenProvider::BEARER_AUTH], $client->getConfig('auth_scheme_preference'));
        $this->assertSame(static::TEST_BEDROCK_BEARER_TOKEN, $client->getToken()->wait()->getToken());
    }

    public function testGivenPromptMessageHasSingleDocumentAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithDocumentAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(FileContent::class, SourceType::URL);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenPromptMessageHasSingleImageAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithImageAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(ImageContent::class, SourceType::BASE64);
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
        $mockProvider = $this->createMockProviderExpectingAttachment(FileContent::class, SourceType::URL);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenAttachmentHasUnknownContentTypeWhenMappingThenThrowsInvalidArgumentException(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithUnknownContentType();
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported attachment content type "unknown_content_type".');

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenPromptMessageHasIdSourceAttachmentWhenPromptingThenContentTypeIsMappedToIdSourceType(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithIdSourceAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(FileContent::class, SourceType::ID);
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenProviderReturnsIdSourceAttachmentWhenPromptingThenAttachmentContentTypeIsId(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningIdSourceAttachment();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertCount(1, $promptResponseTransfer->getMessage()->getAttachments());
        $attachmentTransfer = $promptResponseTransfer->getMessage()->getAttachments()->offsetGet(0);
        $this->assertSame(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE, $attachmentTransfer->getType());
        $this->assertSame(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_ID, $attachmentTransfer->getContentType());
        $this->assertSame('file-abc123', $attachmentTransfer->getContent());
    }

    public function testGivenProviderReturnsReasoningAndTextWhenPromptingThenReasoningAndContentArePopulatedSeparately(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningReasoningAndText();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
        $this->assertSame('Step 1: parse input.' . "\n\n" . 'Step 2: pick branch.', $promptResponseTransfer->getMessage()->getReasoning());
    }

    public function testGivenProviderReturnsTextOnlyWhenPromptingThenReasoningIsNull(): void
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
        $this->assertNull($promptResponseTransfer->getMessage()->getReasoning());
    }

    public function testGivenProviderReturnsReasoningOnlyWhenPromptingThenContentIsNullAndReasoningIsPopulated(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningReasoningOnly();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertNull($promptResponseTransfer->getMessage()->getContent());
        $this->assertSame('Internal scratch pad only.', $promptResponseTransfer->getMessage()->getReasoning());
    }

    public function testGivenDocumentAttachmentHasFilenameWhenPromptingThenFilenameIsForwardedToFileContentBlock(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithDocumentAttachmentAndFilename();
        $mockProvider = $this->createMockProviderExpectingFileContentWithFilename('quarterly-report.pdf');
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $facade->prompt($promptRequestTransfer);
    }

    public function testGivenProviderReturnsFileContentWithFilenameWhenPromptingThenFilenameIsMappedBackToAttachmentTransfer(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningFileContentWithFilename();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertCount(1, $promptResponseTransfer->getMessage()->getAttachments());
        $attachmentTransfer = $promptResponseTransfer->getMessage()->getAttachments()->offsetGet(0);
        $this->assertSame(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT, $attachmentTransfer->getType());
        $this->assertSame('user-manual.pdf', $attachmentTransfer->getFilename());
        $this->assertSame('https://example.com/user-manual.pdf', $attachmentTransfer->getContent());
    }

    public function testGivenImageAttachmentWhenPromptingThenFilenameIsNotSetOnAttachmentTransfer(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningAttachments();
        $facade = $this->createFacadeWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $facade->prompt($promptRequestTransfer);

        // Assert
        $this->assertCount(1, $promptResponseTransfer->getMessage()->getAttachments());
        $attachmentTransfer = $promptResponseTransfer->getMessage()->getAttachments()->offsetGet(0);
        $this->assertSame(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE, $attachmentTransfer->getType());
        $this->assertNull($attachmentTransfer->getFilename());
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
        $assistantMessage = new AssistantMessage(static::TEST_ASSISTANT_RESPONSE);
        $this->setMessageUsage($assistantMessage, $usage);

        $mockProvider->expects($this->once())
            ->method('chat')
            ->willReturn($assistantMessage);

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

    protected function createPromptRequestWithIdSourceAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())
            ->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)
            ->setContent('file-abc123')
            ->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_ID);

        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE)
            ->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
    }

    protected function createPromptRequestWithDocumentAttachmentAndFilename(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())
            ->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)
            ->setContent('https://example.com/quarterly-report.pdf')
            ->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL)
            ->setMediaType('application/pdf')
            ->setFilename('quarterly-report.pdf');

        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE)
            ->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
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

    /**
     * @param class-string<\NeuronAI\Chat\Messages\ContentBlocks\ImageContent|\NeuronAI\Chat\Messages\ContentBlocks\FileContent> $expectedBlockClass
     */
    protected function createMockProviderExpectingAttachment(string $expectedBlockClass, SourceType $expectedSourceType): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (Message $message) use ($expectedBlockClass, $expectedSourceType): bool {
            $attachmentBlocks = $this->extractAttachmentBlocks($message);
            $this->assertCount(1, $attachmentBlocks);
            $this->assertInstanceOf($expectedBlockClass, $attachmentBlocks[0]);
            $this->assertSame($expectedSourceType, $attachmentBlocks[0]->sourceType);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderExpectingMultipleAttachments(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (Message $message): bool {
            $attachmentBlocks = $this->extractAttachmentBlocks($message);
            $this->assertCount(2, $attachmentBlocks);
            $this->assertInstanceOf(FileContent::class, $attachmentBlocks[0]);
            $this->assertInstanceOf(ImageContent::class, $attachmentBlocks[1]);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderExpectingAttachmentWithMediaType(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (Message $message): bool {
            $attachmentBlocks = $this->extractAttachmentBlocks($message);
            $this->assertCount(1, $attachmentBlocks);
            $this->assertSame('image/png', $attachmentBlocks[0]->mediaType);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderReturningAttachments(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $assistantMessage = new AssistantMessage([
            new TextContent(static::TEST_ASSISTANT_RESPONSE),
            new ImageContent('https://example.com/result.png', SourceType::URL, 'image/png'),
        ]);
        $mockProvider->method('chat')->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningIdSourceAttachment(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $assistantMessage = new AssistantMessage([
            new TextContent(static::TEST_ASSISTANT_RESPONSE),
            new ImageContent('file-abc123', SourceType::ID, 'image/png'),
        ]);
        $mockProvider->method('chat')->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningReasoningAndText(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $assistantMessage = new AssistantMessage([
            new ReasoningContent('Step 1: parse input.'),
            new ReasoningContent('Step 2: pick branch.'),
            new TextContent(static::TEST_ASSISTANT_RESPONSE),
        ]);
        $mockProvider->method('chat')->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderReturningReasoningOnly(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $assistantMessage = new AssistantMessage([
            new ReasoningContent('Internal scratch pad only.'),
        ]);
        $mockProvider->method('chat')->willReturn($assistantMessage);

        return $mockProvider;
    }

    protected function createMockProviderExpectingFileContentWithFilename(string $expectedFilename): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->expects($this->once())->method('chat')->with($this->callback(function (Message $message) use ($expectedFilename): bool {
            $attachmentBlocks = $this->extractAttachmentBlocks($message);
            $this->assertCount(1, $attachmentBlocks);
            $this->assertInstanceOf(FileContent::class, $attachmentBlocks[0]);
            $this->assertSame($expectedFilename, $attachmentBlocks[0]->filename);

            return true;
        }))->willReturn(new AssistantMessage(static::TEST_ASSISTANT_RESPONSE));

        return $mockProvider;
    }

    protected function createMockProviderReturningFileContentWithFilename(): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $assistantMessage = new AssistantMessage([
            new TextContent(static::TEST_ASSISTANT_RESPONSE),
            new FileContent('https://example.com/user-manual.pdf', SourceType::URL, 'application/pdf', 'user-manual.pdf'),
        ]);
        $mockProvider->method('chat')->willReturn($assistantMessage);

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
        $reflectionProperty->setValue($message, $usage);
    }

    /**
     * @return array<\NeuronAI\Chat\Messages\ContentBlocks\ImageContent|\NeuronAI\Chat\Messages\ContentBlocks\FileContent>
     */
    protected function extractAttachmentBlocks(Message $message): array
    {
        $attachmentBlocks = [];

        foreach ($message->getContentBlocks() as $contentBlock) {
            if ($contentBlock instanceof ImageContent || $contentBlock instanceof FileContent) {
                $attachmentBlocks[] = $contentBlock;
            }
        }

        return $attachmentBlocks;
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

    /**
     * @param array<string, mixed> $providerConfig
     */
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
        return new NeuronAiMessageMapper(
            $this->createTransferJsonSchemaMapper(),
            new MessageContentExtractor(),
        );
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
