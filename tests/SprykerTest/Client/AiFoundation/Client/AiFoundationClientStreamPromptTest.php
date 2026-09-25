<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\Client;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PromptRequestTransfer;
use Generated\Shared\Transfer\PromptResponseTransfer;
use Spryker\Client\AiFoundation\AiFoundationDependencyProvider;
use Spryker\Client\AiFoundation\Dependency\VendorAdapter\VendorProviderPluginInterface;
use Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use SprykerTest\Client\AiFoundation\AiFoundationClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AiFoundation
 * @group Client
 * @group AiFoundationClientStreamPromptTest
 * Add your own group annotations below this line
 */
class AiFoundationClientStreamPromptTest extends Unit
{
    protected const string AI_CONFIGURATION_NAME = 'anthropic_example';

    protected const string STREAMED_CONTENT = 'Streamed reply';

    protected const string ERROR_MESSAGE_STREAM_FAILED = 'Stream failed.';

    protected AiFoundationClientTester $tester;

    public function testGivenAdapterResponseWhenStreamingPromptThenThatResponseIsReturned(): void
    {
        // Arrange
        $promptResponseTransfer = (new PromptResponseTransfer())
            ->setIsSuccessful(true)
            ->setStreamedContent(static::STREAMED_CONTENT);
        $this->setVendorProviderPluginDependency($this->createVendorAdapterMock($promptResponseTransfer));

        // Act
        $resultTransfer = $this->tester->getClient()->streamPrompt(new PromptRequestTransfer());

        // Assert
        $this->assertSame($promptResponseTransfer, $resultTransfer);
        $this->assertSame(static::STREAMED_CONTENT, $resultTransfer->getStreamedContent());
    }

    public function testGivenPromptRequestWhenStreamingPromptThenRequestIsPassedToAdapter(): void
    {
        // Arrange
        $promptRequestTransfer = (new PromptRequestTransfer())
            ->setAiConfigurationName(static::AI_CONFIGURATION_NAME);
        $vendorAdapterMock = $this->createVendorAdapterMock(new PromptResponseTransfer());
        $this->setVendorProviderPluginDependency($vendorAdapterMock);

        // Assert
        $vendorAdapterMock->expects($this->once())
            ->method('streamPrompt')
            ->with($promptRequestTransfer)
            ->willReturn(new PromptResponseTransfer());

        // Act
        $this->tester->getClient()->streamPrompt($promptRequestTransfer);
    }

    public function testGivenFailingStreamWhenStreamingPromptThenUnsuccessfulResponseIsReturned(): void
    {
        // Arrange
        $promptResponseTransfer = (new PromptResponseTransfer())->setIsSuccessful(false);
        $promptResponseTransfer->addError(
            (new ErrorTransfer())->setMessage(static::ERROR_MESSAGE_STREAM_FAILED),
        );
        $this->setVendorProviderPluginDependency($this->createVendorAdapterMock($promptResponseTransfer));

        // Act
        $resultTransfer = $this->tester->getClient()->streamPrompt(new PromptRequestTransfer());

        // Assert
        $this->assertFalse($resultTransfer->getIsSuccessful());
        $this->assertSame(static::ERROR_MESSAGE_STREAM_FAILED, $resultTransfer->getErrors()->offsetGet(0)->getMessage());
    }

    public function testGivenPromptRequestWhenStreamingPromptThenNoZedCallIsMade(): void
    {
        // Arrange
        $zedRequestClientMock = $this->createMock(ZedRequestClientInterface::class);
        $this->tester->setDependency(AiFoundationDependencyProvider::CLIENT_ZED_REQUEST, $zedRequestClientMock);
        $this->setVendorProviderPluginDependency($this->createVendorAdapterMock(new PromptResponseTransfer()));

        // Assert
        $zedRequestClientMock->expects($this->never())->method('call');

        // Act
        $this->tester->getClient()->streamPrompt(new PromptRequestTransfer());
    }

    /**
     * @return \Spryker\Client\AiFoundation\VendorAdapter\VendorAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createVendorAdapterMock(PromptResponseTransfer $promptResponseTransfer): VendorAdapterInterface
    {
        $vendorAdapterMock = $this->createMock(VendorAdapterInterface::class);
        $vendorAdapterMock->method('streamPrompt')->willReturn($promptResponseTransfer);

        return $vendorAdapterMock;
    }

    protected function setVendorProviderPluginDependency(VendorAdapterInterface $vendorAdapterMock): void
    {
        $vendorProviderPluginMock = $this->createMock(VendorProviderPluginInterface::class);
        $vendorProviderPluginMock->method('getVendorAdapter')->willReturn($vendorAdapterMock);

        $this->tester->setDependency(
            AiFoundationDependencyProvider::PLUGIN_VENDOR_PROVIDER,
            $vendorProviderPluginMock,
        );
    }
}
