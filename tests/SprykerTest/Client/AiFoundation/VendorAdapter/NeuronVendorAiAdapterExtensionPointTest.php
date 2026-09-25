<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AiConfigurationTransfer;
use Generated\Shared\Transfer\ChatSessionResponseTransfer;
use Generated\Shared\Transfer\PromptMessageTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Generated\Shared\Transfer\PromptStreamChunkTransfer;
use RuntimeException;
use Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface;
use Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactory;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ChatHistory\GatewayChatHistoryFactoryInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\ClientNeuronAiToolMapperInterface;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\NeuronAiMessageMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\Mapper\StreamChunkMapper;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\NeuronVendorAiAdapter;
use Spryker\Client\AiFoundation\VendorAdapter\NeuronAI\ProviderResolver\ClientProviderResolverInterface;
use Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface;
use Spryker\Shared\AiFoundation\Stream\PromptStreamChunkType;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group VendorAdapter
 * @group NeuronVendorAiAdapterExtensionPointTest
 * Add your own group annotations below this line
 */
class NeuronVendorAiAdapterExtensionPointTest extends Unit
{
    protected const string AI_CONFIGURATION_NAME = 'test_configuration';

    protected const string PROVIDER_NAME = 'test_provider';

    protected const string CONVERSATION_REFERENCE = 'conversation-reference-test';

    protected const string USER_MESSAGE = 'Hello';

    protected const string ERROR_MESSAGE_PLUGIN_BROKEN = 'Plugin is broken.';

    /**
     * @var array<int, string>
     */
    protected const array STREAM_TOKENS = ['Hel', 'lo ', 'world'];

    protected AiFoundationClientTester $tester;

    public function testGivenStreamedTokensWhenStreamingPromptThenStreamEventPluginReceivesOneChunkPerToken(): void
    {
        // Arrange
        $receivedChunkTransfers = [];
        $streamEventPlugin = $this->createRecordingStreamEventPlugin($receivedChunkTransfers);

        // Act
        $this->createAdapter(streamEventPlugins: [$streamEventPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertSame(static::STREAM_TOKENS, array_map(
            static fn (PromptStreamChunkTransfer $chunkTransfer): string => (string)$chunkTransfer->getContent(),
            $receivedChunkTransfers,
        ));
    }

    public function testGivenStreamedTokensWhenStreamingPromptThenStreamEventPluginReceivesTextChunkType(): void
    {
        // Arrange
        $receivedChunkTransfers = [];
        $streamEventPlugin = $this->createRecordingStreamEventPlugin($receivedChunkTransfers);

        // Act
        $this->createAdapter(streamEventPlugins: [$streamEventPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertSame(PromptStreamChunkType::Text->value, $receivedChunkTransfers[0]->getType());
    }

    public function testGivenPromptRequestWhenStreamingPromptThenStreamEventPluginReceivesThatRequest(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $receivedRequestTransfers = [];
        $streamEventPlugin = $this->createMock(StreamEventPluginInterface::class);
        $streamEventPlugin->method('onStreamEvent')
            ->willReturnCallback(function (PromptStreamChunkTransfer $chunkTransfer, PromptRequestTransfer $requestTransfer) use (&$receivedRequestTransfers): void {
                $receivedRequestTransfers[] = $requestTransfer;
            });

        // Act
        $this->createAdapter(streamEventPlugins: [$streamEventPlugin])->streamPrompt($promptRequestTransfer);

        // Assert
        $this->assertSame($promptRequestTransfer, $receivedRequestTransfers[0]);
    }

    public function testGivenSeveralStreamEventPluginsWhenStreamingPromptThenEveryPluginIsNotified(): void
    {
        // Arrange
        $firstReceivedChunkTransfers = [];
        $secondReceivedChunkTransfers = [];

        // Act
        $this->createAdapter(streamEventPlugins: [
            $this->createRecordingStreamEventPlugin($firstReceivedChunkTransfers),
            $this->createRecordingStreamEventPlugin($secondReceivedChunkTransfers),
        ])->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertCount(count(static::STREAM_TOKENS), $firstReceivedChunkTransfers);
        $this->assertCount(count(static::STREAM_TOKENS), $secondReceivedChunkTransfers);
    }

    public function testGivenThrowingStreamEventPluginWhenStreamingPromptThenTurnSucceeds(): void
    {
        // Arrange
        $streamEventPlugin = $this->createMock(StreamEventPluginInterface::class);
        $streamEventPlugin->method('onStreamEvent')
            ->willThrowException(new RuntimeException(static::ERROR_MESSAGE_PLUGIN_BROKEN));

        // Act
        $promptResponseTransfer = $this->createAdapter(streamEventPlugins: [$streamEventPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(implode('', static::STREAM_TOKENS), $promptResponseTransfer->getStreamedContent());
    }

    public function testGivenThrowingStreamEventPluginWhenStreamingPromptThenRemainingPluginsAreNotified(): void
    {
        // Arrange
        $receivedChunkTransfers = [];
        $throwingStreamEventPlugin = $this->createMock(StreamEventPluginInterface::class);
        $throwingStreamEventPlugin->method('onStreamEvent')
            ->willThrowException(new RuntimeException(static::ERROR_MESSAGE_PLUGIN_BROKEN));

        // Act
        $this->createAdapter(streamEventPlugins: [
            $throwingStreamEventPlugin,
            $this->createRecordingStreamEventPlugin($receivedChunkTransfers),
        ])->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertCount(count(static::STREAM_TOKENS), $receivedChunkTransfers);
    }

    public function testGivenPostPromptPluginWhenStreamingPromptThenItReceivesRequestAndCompletedResponse(): void
    {
        // Arrange
        $promptRequestTransfer = $this->createPromptRequestTransfer();
        $receivedRequestTransfer = null;
        $receivedResponseTransfer = null;

        $postPromptPlugin = $this->createMock(PostPromptPluginInterface::class);
        $postPromptPlugin->expects($this->once())
            ->method('postPrompt')
            ->willReturnCallback(function (PromptRequestTransfer $requestTransfer, PromptResponseTransfer $responseTransfer) use (&$receivedRequestTransfer, &$receivedResponseTransfer): void {
                $receivedRequestTransfer = $requestTransfer;
                $receivedResponseTransfer = $responseTransfer;
            });

        // Act
        $promptResponseTransfer = $this->createAdapter(postPromptPlugins: [$postPromptPlugin])
            ->streamPrompt($promptRequestTransfer);

        // Assert
        $this->assertSame($promptRequestTransfer, $receivedRequestTransfer);
        $this->assertSame($promptResponseTransfer, $receivedResponseTransfer);
        $this->assertSame(implode('', static::STREAM_TOKENS), $receivedResponseTransfer->getStreamedContent());
    }

    public function testGivenThrowingPostPromptPluginWhenStreamingPromptThenTurnSucceeds(): void
    {
        // Arrange
        $postPromptPlugin = $this->createMock(PostPromptPluginInterface::class);
        $postPromptPlugin->method('postPrompt')
            ->willThrowException(new RuntimeException(static::ERROR_MESSAGE_PLUGIN_BROKEN));

        // Act
        $promptResponseTransfer = $this->createAdapter(postPromptPlugins: [$postPromptPlugin])
            ->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
        $this->assertSame(implode('', static::STREAM_TOKENS), $promptResponseTransfer->getStreamedContent());
    }

    public function testGivenRejectedSessionWhenStreamingPromptThenNoPluginIsExecuted(): void
    {
        // Arrange
        $streamEventPlugin = $this->createMock(StreamEventPluginInterface::class);
        $postPromptPlugin = $this->createMock(PostPromptPluginInterface::class);

        // Assert
        $streamEventPlugin->expects($this->never())->method('onStreamEvent');
        $postPromptPlugin->expects($this->never())->method('postPrompt');

        // Act
        $promptResponseTransfer = $this->createAdapter(
            chatSessionResponseTransfer: (new ChatSessionResponseTransfer())->setIsSuccessful(false),
            postPromptPlugins: [$postPromptPlugin],
            streamEventPlugins: [$streamEventPlugin],
        )->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertFalse($promptResponseTransfer->getIsSuccessful());
    }

    public function testGivenNoConversationReferenceWhenStreamingPromptThenChatHistoryIsNotSaved(): void
    {
        // Arrange
        $aiFoundationStubMock = $this->createMock(AiFoundationStubInterface::class);

        // Assert
        $aiFoundationStubMock->expects($this->never())->method('saveConversationHistoryCollection');

        // Act
        $promptResponseTransfer = $this->createAdapter(aiFoundationStubMock: $aiFoundationStubMock)
            ->streamPrompt($this->createPromptRequestTransfer()->setConversationReference(null));

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
    }

    public function testGivenFailingChatHistorySaveWhenStreamingPromptThenPostPromptPluginsStillRun(): void
    {
        // Arrange
        $aiFoundationStubMock = $this->createMock(AiFoundationStubInterface::class);
        $aiFoundationStubMock->method('saveConversationHistoryCollection')
            ->willThrowException(new RuntimeException(static::ERROR_MESSAGE_PLUGIN_BROKEN));

        $postPromptPlugin = $this->createMock(PostPromptPluginInterface::class);

        // Assert
        $postPromptPlugin->expects($this->once())->method('postPrompt');

        // Act
        $promptResponseTransfer = $this->createAdapter(
            postPromptPlugins: [$postPromptPlugin],
            aiFoundationStubMock: $aiFoundationStubMock,
        )->streamPrompt($this->createPromptRequestTransfer());

        // Assert
        $this->assertTrue($promptResponseTransfer->getIsSuccessful());
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\PromptStreamChunkTransfer> $receivedChunkTransfers
     */
    protected function createRecordingStreamEventPlugin(array &$receivedChunkTransfers): StreamEventPluginInterface
    {
        $streamEventPlugin = $this->createMock(StreamEventPluginInterface::class);
        $streamEventPlugin->method('onStreamEvent')
            ->willReturnCallback(function (PromptStreamChunkTransfer $chunkTransfer) use (&$receivedChunkTransfers): void {
                $receivedChunkTransfers[] = $chunkTransfer;
            });

        return $streamEventPlugin;
    }

    protected function createPromptRequestTransfer(): PromptRequestTransfer
    {
        return (new PromptRequestTransfer())
            ->setAiConfigurationName(static::AI_CONFIGURATION_NAME)
            ->setConversationReference(static::CONVERSATION_REFERENCE)
            ->setPromptMessage((new PromptMessageTransfer())->setContent(static::USER_MESSAGE));
    }

    /**
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\PostPromptPluginInterface> $postPromptPlugins
     * @param array<\Spryker\Client\AiFoundation\Dependency\Plugin\StreamEventPluginInterface> $streamEventPlugins
     * @param (\Spryker\Client\AiFoundation\Zed\AiFoundationStubInterface&\PHPUnit\Framework\MockObject\MockObject)|null $aiFoundationStubMock
     */
    protected function createAdapter(
        ?ChatSessionResponseTransfer $chatSessionResponseTransfer = null,
        array $postPromptPlugins = [],
        array $streamEventPlugins = [],
        ?AiFoundationStubInterface $aiFoundationStubMock = null
    ): NeuronVendorAiAdapter {
        $chatSessionResponseTransfer ??= $this->createSuccessfulChatSessionResponseTransfer();

        $aiFoundationStubMock ??= $this->createMock(AiFoundationStubInterface::class);
        $aiFoundationStubMock->method('initChatSession')->willReturn($chatSessionResponseTransfer);

        $clientProviderResolverMock = $this->createMock(ClientProviderResolverInterface::class);
        $clientProviderResolverMock->method('resolve')->willReturn(new ScriptedAiProvider(static::STREAM_TOKENS));

        return new NeuronVendorAiAdapter(
            $aiFoundationStubMock,
            $this->createGatewayChatHistoryFactoryMock($aiFoundationStubMock),
            $clientProviderResolverMock,
            new NeuronAiMessageMapper(),
            $this->createMock(ClientNeuronAiToolMapperInterface::class),
            new StreamChunkMapper(),
            [],
            $postPromptPlugins,
            [],
            [],
            $streamEventPlugins,
        );
    }

    protected function createSuccessfulChatSessionResponseTransfer(): ChatSessionResponseTransfer
    {
        return (new ChatSessionResponseTransfer())
            ->setIsSuccessful(true)
            ->setAiConfiguration(
                (new AiConfigurationTransfer())
                    ->setName(static::AI_CONFIGURATION_NAME)
                    ->setProviderName(static::PROVIDER_NAME)
                    ->setProviderConfig([]),
            );
    }

    protected function createGatewayChatHistoryFactoryMock(
        AiFoundationStubInterface $aiFoundationStubMock
    ): GatewayChatHistoryFactoryInterface {
        return new GatewayChatHistoryFactory($aiFoundationStubMock);
    }
}
