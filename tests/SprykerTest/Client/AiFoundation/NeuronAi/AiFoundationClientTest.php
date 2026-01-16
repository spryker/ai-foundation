<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AiFoundation\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttachmentTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Attachments\Attachment;
use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\Client\AiFoundation\AiFoundationClient;
use Spryker\Client\AiFoundation\AiFoundationClientInterface;
use Spryker\Client\AiFoundation\AiFoundationConfig;
use Spryker\Client\AiFoundation\AiFoundationFactory;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapper;
use Spryker\Client\AiFoundation\Mapper\TransferJsonSchemaMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group NeuronAi
 * @group AiFoundationClientTest
 * Add your own group annotations below this line
 */
class AiFoundationClientTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_AI_ENGINE = 'test_ollama';

    /**
     * @var string
     */
    protected const TEST_OLLAMA_URL = 'http://localhost:11434/api';

    /**
     * @var string
     */
    protected const TEST_OLLAMA_MODEL = 'llama3.2';

    /**
     * @var string
     */
    protected const TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    /**
     * @var string
     */
    protected const TEST_USER_MESSAGE = 'Hello, AI!';

    /**
     * @var string
     */
    protected const TEST_ASSISTANT_RESPONSE = 'Hello! How can I help you today?';

    /**
     * @var \SprykerTest\Client\AiFoundation\AiFoundationClientTester
     */
    protected AiFoundationClientTester $tester;

    /**
     * @return void
     */
    public function testPromptWithOllamaProviderCreatesProviderWithCorrectConfigAndCallsChat(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockAiProvider();
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertNotNull($promptResponseTransfer->getMessage());
        $this->assertSame(static::TEST_ASSISTANT_RESPONSE, $promptResponseTransfer->getMessage()->getContent());
    }

    /**
     * @return void
     */
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

        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
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

        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
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

        $client = $this->createClientWithMockedProviderResolver($mockProviderResolver);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @dataProvider providerResolvingDataProvider
     *
     * @param string $providerName
     * @param array<string, mixed> $providerConfig
     *
     * @return void
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

        $client = $this->createClientWithMockedProviderResolverAndConfig(
            $mockProviderResolver,
            $aiEngineName,
            $providerName,
            $providerConfig,
        );

        // Act
        $promptResponseTransfer = $client->prompt($promptRequestTransfer);

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

    /**
     * @param string $aiEngineName
     *
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestTransferWithEngine(string $aiEngineName): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName($aiEngineName)
            ->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @param \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface $mockProviderResolver
     * @param string $aiEngineName
     * @param string $providerName
     * @param array<string, mixed> $providerConfig
     *
     * @return \Spryker\Client\AiFoundation\AiFoundationClientInterface
     */
    protected function createClientWithMockedProviderResolverAndConfig(
        ProviderResolverInterface $mockProviderResolver,
        string $aiEngineName,
        string $providerName,
        array $providerConfig,
    ): AiFoundationClientInterface {
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
            aiConfigurations: $config->getAiConfigurations(),
            toolMapper: $this->createNeuronAiToolMapper(),
        );

        $factoryMock = $this->createMock(AiFoundationFactory::class);
        $factoryMock->method('createVendorAdapter')->willReturn($neuronAiAdapter);

        $client = new AiFoundationClient();
        $client->setFactory($factoryMock);

        return $client;
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \NeuronAI\Providers\AIProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
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
     * @param \NeuronAI\Providers\AIProviderInterface $mockProvider
     *
     * @return \Spryker\Client\AiFoundation\AiFoundationClientInterface
     */
    protected function createClientWithMockedProvider(AIProviderInterface $mockProvider): AiFoundationClientInterface
    {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        return $this->createClientWithMockedProviderResolver($mockProviderResolver);
    }

    /**
     * @param \Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ProviderResolverInterface $mockProviderResolver
     *
     * @return \Spryker\Client\AiFoundation\AiFoundationClientInterface
     */
    protected function createClientWithMockedProviderResolver(ProviderResolverInterface $mockProviderResolver): AiFoundationClientInterface
    {
        $config = $this->createMockConfig();

        $neuronAiAdapter = new NeuronVendorAiAdapter(
            providerResolver: $mockProviderResolver,
            messageMapper: $this->createNeuronAiMessageMapper(),
            aiConfigurations: $config->getAiConfigurations(),
            toolMapper: $this->createNeuronAiToolMapper(),
        );

        $factoryMock = $this->createMock(AiFoundationFactory::class);
        $factoryMock->method('createVendorAdapter')
            ->willReturn($neuronAiAdapter);

        $client = new AiFoundationClient();
        $client->setFactory($factoryMock);

        return $client;
    }

    /**
     * @return \Spryker\Client\AiFoundation\AiFoundationConfig|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @return void
     */
    public function testGivenPromptMessageHasSingleDocumentAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithDocumentAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
    public function testGivenPromptMessageHasSingleImageAttachmentWhenPromptingThenAttachmentIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithImageAttachment();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::IMAGE, AttachmentContentType::BASE64);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
    public function testGivenPromptMessageHasMultipleAttachmentsWhenPromptingThenAllAttachmentsAreMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithMultipleAttachments();
        $mockProvider = $this->createMockProviderExpectingMultipleAttachments();
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
    public function testGivenPromptMessageHasAttachmentWithMediaTypeWhenPromptingThenMediaTypeIsMappedCorrectly(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithAttachmentWithMediaType();
        $mockProvider = $this->createMockProviderExpectingAttachmentWithMediaType();
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
    public function testGivenProviderReturnsMessageWithAttachmentsWhenPromptingThenAttachmentsAreMappedToResponse(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $mockProvider = $this->createMockProviderReturningAttachments();
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponseTransfer = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertCount(1, $promptResponseTransfer->getMessage()->getAttachments());
        $this->assertAttachmentMatchesExpectedValues($promptResponseTransfer->getMessage()->getAttachments()->offsetGet(0));
    }

    /**
     * @return void
     */
    public function testGivenAttachmentHasUnknownTypeWhenMappingThenDefaultsToDocumentType(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithUnknownAttachmentType();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return void
     */
    public function testGivenAttachmentHasUnknownContentTypeWhenMappingThenDefaultsToUrlContentType(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestWithUnknownContentType();
        $mockProvider = $this->createMockProviderExpectingAttachment(AttachmentType::DOCUMENT, AttachmentContentType::URL);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithDocumentAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = $this->createDocumentAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithImageAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = $this->createImageAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithMultipleAttachments(): PromptRequestTransfer
    {
        $documentAttachment = $this->createDocumentAttachment();
        $imageAttachment = $this->createImageAttachment();
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($documentAttachment)->addAttachment($imageAttachment);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithIdAttachment(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)->setContent('document-id-123')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_ID);
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithAttachmentWithMediaType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE)->setContent('https://example.com/image.png')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL)->setMediaType('image/png');
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithUnknownAttachmentType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType('unknown_type')->setContent('https://example.com/document.pdf')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL);
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PromptRequestTransfer
     */
    protected function createPromptRequestWithUnknownContentType(): PromptRequestTransfer
    {
        $attachmentTransfer = (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)->setContent('https://example.com/document.pdf')->setContentType('unknown_content_type');
        $promptMessageTransfer = (new PromptMessageTransfer())->setContent(static::TEST_USER_MESSAGE)->addAttachment($attachmentTransfer);

        return (new PromptRequestTransfer())->setAiConfigurationName(static::TEST_AI_ENGINE)->setPromptMessage($promptMessageTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\AttachmentTransfer
     */
    protected function createDocumentAttachment(): AttachmentTransfer
    {
        return (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_DOCUMENT)->setContent('https://example.com/document.pdf')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL);
    }

    /**
     * @return \Generated\Shared\Transfer\AttachmentTransfer
     */
    protected function createImageAttachment(): AttachmentTransfer
    {
        return (new AttachmentTransfer())->setType(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE)->setContent('base64EncodedImageData')->setContentType(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_BASE64);
    }

    /**
     * @param \NeuronAI\Chat\Enums\AttachmentType $expectedType
     * @param \NeuronAI\Chat\Enums\AttachmentContentType $expectedContentType
     *
     * @return \NeuronAI\Providers\AIProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @return \NeuronAI\Providers\AIProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @return \NeuronAI\Providers\AIProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @return \NeuronAI\Providers\AIProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @param \Generated\Shared\Transfer\AttachmentTransfer $attachmentTransfer
     *
     * @return void
     */
    protected function assertAttachmentMatchesExpectedValues(AttachmentTransfer $attachmentTransfer): void
    {
        $this->assertSame(AiFoundationConstants::ATTACHMENT_TYPE_IMAGE, $attachmentTransfer->getType());
        $this->assertSame('https://example.com/result.png', $attachmentTransfer->getContent());
        $this->assertSame(AiFoundationConstants::ATTACHMENT_CONTENT_TYPE_URL, $attachmentTransfer->getContentType());
        $this->assertSame('image/png', $attachmentTransfer->getMediaType());
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
}
