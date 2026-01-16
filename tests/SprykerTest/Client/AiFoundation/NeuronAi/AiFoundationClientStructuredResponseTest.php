<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AiFoundation\NeuronAi;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use NeuronAI\Chat\Messages\AssistantMessage;
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
use SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseBranchInfoTransfer;
use SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponsePathTransfer;
use SprykerTest\Client\AiFoundation\NeuronAi\Transfers\AiResponseTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group NeuronAi
 * @group AiFoundationClientStructuredResponseTest
 * Add your own group annotations below this line
 */
class AiFoundationClientStructuredResponseTest extends Unit
{
    protected const string TEST_AI_ENGINE = 'test_ollama';

    protected const string TEST_OLLAMA_URL = 'http://localhost:11434/api';

    protected const string TEST_OLLAMA_MODEL = 'llama3.2';

    protected const string TEST_SYSTEM_PROMPT = 'You are a test assistant.';

    protected const string TEST_USER_MESSAGE = 'Analyze this code branch.';

    protected AiFoundationClientTester $tester;

    public function testGivenValidStructuredResponseWhenPromptStructuredThenTransferIsPopulated(): void
    {
        // Arrange
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema);

        $responseJson = json_encode([
            'rand_string' => 'Test String Value',
            'any_object' => [
                'branch' => 'feature/test-branch',
                'message' => 'Test message',
            ],
            'array_of_strings' => ['php', 'spryker', 'testing'],
            'ai_response_paths' => [
                ['path' => '/src/path/one.php'],
                ['path' => '/src/path/two.php'],
            ],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($responseJson);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertCount(0, $promptResponse->getErrors());

        $result = $promptResponse->getStructuredMessage();
        $this->assertInstanceOf(AiResponseTransfer::class, $result);
        $this->assertSame('Test String Value', $result->getRandString());
        $this->assertInstanceOf(AiResponseBranchInfoTransfer::class, $result->getAnyObject());
        $this->assertSame('feature/test-branch', $result->getAnyObject()->getBranch());
        $this->assertSame('Test message', $result->getAnyObject()->getMessage());
        $this->assertCount(3, $result->getArrayOfStrings());
        $this->assertSame(['php', 'spryker', 'testing'], $result->getArrayOfStrings());
        $this->assertCount(2, $result->getAiResponsePaths());
        $this->assertSame('/src/path/one.php', $result->getAiResponsePaths()->offsetGet(0)->getPath());
        $this->assertSame('/src/path/two.php', $result->getAiResponsePaths()->offsetGet(1)->getPath());
    }

    public function testGivenValidJsonSchemaWhenMappingTransferThenCorrectSchemaIsGenerated(): void
    {
        // Arrange
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema);

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $mockProvider->expects($this->once())
            ->method('structured')
            ->with(
                $this->anything(),
                AiResponseTransfer::class,
                $this->callback(function (array $schema): bool {
                    $this->assertArrayHasKey('type', $schema);
                    $this->assertSame('object', $schema['type']);
                    $this->assertArrayHasKey('properties', $schema);
                    $this->assertArrayHasKey('rand_string', $schema['properties']);
                    $this->assertArrayHasKey('any_object', $schema['properties']);
                    $this->assertArrayHasKey('array_of_strings', $schema['properties']);
                    $this->assertArrayHasKey('ai_response_paths', $schema['properties']);

                    $this->assertSame('string', $schema['properties']['rand_string']['type']);
                    $this->assertSame('array', $schema['properties']['array_of_strings']['type']);
                    $this->assertSame('array', $schema['properties']['ai_response_paths']['type']);
                    $this->assertArrayHasKey('items', $schema['properties']['ai_response_paths']);

                    return true;
                }),
            )
            ->willReturn(new AssistantMessage($this->createValidStructuredResponseJson()));

        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $client->prompt($promptRequestTransfer);
    }

    public function testGivenMissingRequiredPropertyWhenPromptStructuredThenReturnsErrorResponse(): void
    {
        // Arrange
        $maxRetries = 3;
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema)
            ->setMaxRetries($maxRetries);

        $responseJsonWithMissingProperty = json_encode([
            'any_object' => [
                'branch' => 'test-branch',
                'message' => 'Test message',
            ],
            'array_of_strings' => ['test'],
            'ai_response_paths' => [
                ['path' => '/src/path.php'],
            ],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($responseJsonWithMissingProperty);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertFalse($promptResponse->getIsSuccessful());
        $this->assertCount($maxRetries, $promptResponse->getErrors());
        $this->assertStringContainsString('Failed to map structured response', $promptResponse->getErrors()->offsetGet(0)->getMessage());
        $this->assertStringContainsString('Attempt 1 failed', $promptResponse->getErrors()->offsetGet(0)->getMessage());
        $this->assertStringContainsString('Attempt 2 failed', $promptResponse->getErrors()->offsetGet(1)->getMessage());
        $this->assertStringContainsString('Attempt 3 failed', $promptResponse->getErrors()->offsetGet(2)->getMessage());
    }

    public function testGivenEmptyCollectionWhenPromptStructuredThenReturnsErrorResponse(): void
    {
        // Arrange
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema);

        $responseJsonWithEmptyCollection = json_encode([
            'rand_string' => 'Test String',
            'any_object' => [
                'branch' => 'test-branch',
                'message' => 'Test message',
            ],
            'array_of_strings' => ['test'],
            'ai_response_paths' => [],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($responseJsonWithEmptyCollection);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertFalse($promptResponse->getIsSuccessful());
        $this->assertCount(1, $promptResponse->getErrors());
        $this->assertStringContainsString('empty collection', $promptResponse->getErrors()->offsetGet(0)->getMessage());
    }

    public function testGivenInvalidJsonResponseWhenPromptStructuredThenRetriesThreeTimes(): void
    {
        // Arrange
        $maxRetries = 3;
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema)
            ->setMaxRetries($maxRetries);

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $mockProvider->expects($this->exactly($maxRetries))
            ->method('structured')
            ->willReturn(new AssistantMessage('invalid json'));

        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertFalse($promptResponse->getIsSuccessful());
        $this->assertCount($maxRetries, $promptResponse->getErrors());
    }

    public function testGivenFailureThenSuccessWhenPromptStructuredThenSucceedsOnSecondAttempt(): void
    {
        // Arrange
        $maxRetries = 2;
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema)
            ->setMaxRetries($maxRetries);

        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();

        $validResponse = new AssistantMessage($this->createValidStructuredResponseJson());

        $mockProvider->expects($this->exactly($maxRetries))
            ->method('structured')
            ->willReturnOnConsecutiveCalls(
                new AssistantMessage('invalid json'),
                $validResponse,
            );

        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertTrue($promptResponse->getIsSuccessful());
        $this->assertCount($maxRetries - 1, $promptResponse->getErrors());

        $result = $promptResponse->getStructuredMessage();
        $this->assertInstanceOf(AiResponseTransfer::class, $result);
        $this->assertNotNull($result->getRandString());
    }

    public function testGivenStructuredResponseErrorWhenPromptStructuredThenErrorContainsDetails(): void
    {
        // Arrange
        $maxRetries = 3;
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema)
            ->setMaxRetries($maxRetries);

        $invalidResponseJson = json_encode([
            'any_object' => [
                'branch' => 'test-branch',
                'message' => 'Test message',
            ],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($invalidResponseJson);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);

        // Assert
        $this->assertFalse($promptResponse->getIsSuccessful());
        $this->assertCount($maxRetries, $promptResponse->getErrors());

        $error = $promptResponse->getErrors()->offsetGet(0);
        $this->assertStringContainsString('Failed to map structured response', $error->getMessage());
        $this->assertStringContainsString('Attempt 1 failed', $error->getMessage());
        $this->assertStringContainsString('Response content:', $error->getMessage());
        $this->assertStringContainsString('AiResponseTransfer', $error->getEntityIdentifier());

        $this->assertStringContainsString('Attempt 2 failed', $promptResponse->getErrors()->offsetGet(1)->getMessage());
        $this->assertStringContainsString('Attempt 3 failed', $promptResponse->getErrors()->offsetGet(2)->getMessage());
    }

    public function testGivenNestedTransferObjectWhenPromptStructuredThenNestedObjectIsPopulated(): void
    {
        // Arrange
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema);

        $responseJson = json_encode([
            'rand_string' => 'Nested Test',
            'any_object' => [
                'branch' => 'feature/nested-branch',
                'message' => 'Testing nested object mapping',
            ],
            'array_of_strings' => ['nested'],
            'ai_response_paths' => [
                ['path' => '/src/module.php'],
            ],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($responseJson);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);
        $result = $promptResponse->getStructuredMessage();

        // Assert
        $this->assertInstanceOf(AiResponseBranchInfoTransfer::class, $result->getAnyObject());
        $this->assertSame('feature/nested-branch', $result->getAnyObject()->getBranch());
        $this->assertSame('Testing nested object mapping', $result->getAnyObject()->getMessage());
    }

    public function testGivenCollectionOfTransfersWhenPromptStructuredThenAllItemsArePopulated(): void
    {
        // Arrange
        $structuredSchema = new AiResponseTransfer();
        $promptRequestTransfer = $this->createPromptRequestTransfer()
            ->setStructuredMessage($structuredSchema);

        $responseJson = json_encode([
            'rand_string' => 'Collection Test',
            'any_object' => [
                'branch' => 'feature/collection-branch',
                'message' => 'Testing collection of paths',
            ],
            'array_of_strings' => ['collection', 'multiple'],
            'ai_response_paths' => [
                ['path' => '/src/path/one.php'],
                ['path' => '/src/path/two.php'],
                ['path' => '/src/path/three.php'],
            ],
        ]);

        $mockProvider = $this->createMockProviderReturningStructuredResponse($responseJson);
        $client = $this->createClientWithMockedProvider($mockProvider);

        // Act
        $promptResponse = $client->prompt($promptRequestTransfer);
        $result = $promptResponse->getStructuredMessage();

        // Assert
        $this->assertCount(3, $result->getAiResponsePaths());

        foreach ($result->getAiResponsePaths() as $pathTransfer) {
            $this->assertInstanceOf(AiResponsePathTransfer::class, $pathTransfer);
            $this->assertNotEmpty($pathTransfer->getPath());
        }

        $this->assertSame('/src/path/one.php', $result->getAiResponsePaths()->offsetGet(0)->getPath());
        $this->assertSame('/src/path/two.php', $result->getAiResponsePaths()->offsetGet(1)->getPath());
        $this->assertSame('/src/path/three.php', $result->getAiResponsePaths()->offsetGet(2)->getPath());
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        $promptMessageTransfer = (new PromptMessageTransfer())
            ->setContent(static::TEST_USER_MESSAGE);

        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::TEST_AI_ENGINE)
            ->setPromptMessage($promptMessageTransfer);
    }

    protected function createClientWithMockedProvider(AIProviderInterface $mockProvider): AiFoundationClientInterface
    {
        $mockProviderResolver = $this->createMock(ProviderResolverInterface::class);
        $mockProviderResolver->method('resolve')->willReturn($mockProvider);

        $config = $this->createMockConfig();

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

    protected function createMockProviderReturningStructuredResponse(string $responseJson): AIProviderInterface
    {
        $mockProvider = $this->createMock(AIProviderInterface::class);
        $mockProvider->method('systemPrompt')->willReturnSelf();
        $mockProvider->method('structured')->willReturn(new AssistantMessage($responseJson));

        return $mockProvider;
    }

    protected function createValidStructuredResponseJson(): string
    {
        return json_encode([
            'rand_string' => 'AI Foundation Implementation',
            'any_object' => [
                'branch' => 'feature/structured-response',
                'message' => 'Structured response implementation for AI foundation',
            ],
            'array_of_strings' => ['ai', 'foundation', 'structured-response'],
            'ai_response_paths' => [
                ['path' => '/src/AiFoundationClient.php'],
                ['path' => '/src/NeuronVendorAiAdapter.php'],
            ],
        ]);
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
