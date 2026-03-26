<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AiFoundation\Business;

use Codeception\Test\Unit;
use Spryker\Shared\AiFoundation\AiFoundationConstants;
use Spryker\Zed\AiFoundation\AiFoundationConfig;
use Spryker\Zed\AiFoundation\Exception\AiFoundationConfigurationResolverException;
use SprykerTest\Zed\AiFoundation\AiFoundationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AiFoundation
 * @group Business
 * @group AiFoundationConfigTest
 * Add your own group annotations below this line
 */
class AiFoundationConfigTest extends Unit
{
    protected AiFoundationBusinessTester $tester;

    public function testGivenStaticConfigurationValuesWhenGettingAiConfigurationsThenValuesAreReturnedUnchanged(): void
    {
        // Arrange
        $configurations = [
            AiFoundationConstants::AI_CONFIGURATION_DEFAULT => [
                AiFoundationConstants::AI_PROVIDER_NAME => AiFoundationConstants::PROVIDER_OPENAI,
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => 'static-api-key',
                    'model' => 'gpt-4o-mini',
                ],
            ],
        ];

        $config = $this->createConfigMockWithModuleConfig($configurations, []);

        // Act
        $result = $config->getAiConfigurations();

        // Assert
        $this->assertSame($configurations, $result);
    }

    public function testGivenConfigurationReferenceWhenGettingAiConfigurationsThenPrefixedValueIsResolved(): void
    {
        // Arrange
        $configurations = [
            AiFoundationConstants::AI_CONFIGURATION_DEFAULT => [
                AiFoundationConstants::AI_PROVIDER_NAME => AiFoundationConstants::PROVIDER_OPENAI,
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'ai:provider:api_key',
                    'model' => 'gpt-4o-mini',
                ],
            ],
        ];

        $moduleConfigValues = [
            'ai:provider:api_key' => 'resolved-api-key-value',
        ];

        $config = $this->createConfigMockWithModuleConfig($configurations, $moduleConfigValues);

        // Act
        $result = $config->getAiConfigurations();

        // Assert
        $this->assertSame('resolved-api-key-value', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['key']);
        $this->assertSame('gpt-4o-mini', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['model']);
    }

    public function testGivenNestedConfigurationReferencesWhenGettingAiConfigurationsThenAllNestedValuesAreResolved(): void
    {
        // Arrange
        $configurations = [
            AiFoundationConstants::AI_CONFIGURATION_DEFAULT => [
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'ai:provider:api_key',
                    'model' => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'ai:provider:model',
                ],
                AiFoundationConstants::AI_CONFIG_SYSTEM_PROMPT => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'ai:provider:prompt',
            ],
        ];

        $moduleConfigValues = [
            'ai:provider:api_key' => 'resolved-key',
            'ai:provider:model' => 'resolved-model',
            'ai:provider:prompt' => 'resolved-prompt',
        ];

        $config = $this->createConfigMockWithModuleConfig($configurations, $moduleConfigValues);

        // Act
        $result = $config->getAiConfigurations();

        // Assert
        $this->assertSame('resolved-key', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['key']);
        $this->assertSame('resolved-model', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['model']);
        $this->assertSame('resolved-prompt', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_CONFIG_SYSTEM_PROMPT]);
    }

    public function testGivenUnresolvableConfigurationReferenceWhenGettingAiConfigurationsThenExceptionIsThrown(): void
    {
        // Arrange
        $configurations = [
            AiFoundationConstants::AI_CONFIGURATION_DEFAULT => [
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'nonexistent:setting:key',
                ],
            ],
        ];

        $config = $this->createConfigMockWithModuleConfig($configurations, []);

        // Expect
        $this->expectException(AiFoundationConfigurationResolverException::class);
        $this->expectExceptionMessageMatches('/nonexistent:setting:key/');

        // Act
        $config->getAiConfigurations();
    }

    public function testGivenMixedStaticAndDynamicValuesWhenGettingAiConfigurationsThenStaticValuesRemainUnchangedAndReferencesAreResolved(): void
    {
        // Arrange
        $configurations = [
            AiFoundationConstants::AI_CONFIGURATION_DEFAULT => [
                AiFoundationConstants::AI_PROVIDER_NAME => AiFoundationConstants::PROVIDER_OPENAI,
                AiFoundationConstants::AI_PROVIDER_CONFIG => [
                    'key' => AiFoundationConstants::CONFIGURATION_REFERENCE_PREFIX . 'ai:api_key',
                    'model' => 'gpt-4o-mini',
                    'parameters' => [],
                ],
            ],
        ];

        $moduleConfigValues = [
            'ai:api_key' => 'dynamic-api-key',
        ];

        $config = $this->createConfigMockWithModuleConfig($configurations, $moduleConfigValues);

        // Act
        $result = $config->getAiConfigurations();

        // Assert
        $this->assertSame(AiFoundationConstants::PROVIDER_OPENAI, $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_NAME]);
        $this->assertSame('dynamic-api-key', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['key']);
        $this->assertSame('gpt-4o-mini', $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['model']);
        $this->assertSame([], $result[AiFoundationConstants::AI_CONFIGURATION_DEFAULT][AiFoundationConstants::AI_PROVIDER_CONFIG]['parameters']);
    }

    protected function createConfigMockWithModuleConfig(array $configurations, array $moduleConfigValues): AiFoundationConfig
    {
        $config = $this->getMockBuilder(AiFoundationConfig::class)
            ->onlyMethods(['get', 'getModuleConfig'])
            ->getMock();

        $config->method('get')
            ->with(AiFoundationConstants::AI_CONFIGURATIONS)
            ->willReturn($configurations);

        $config->method('getModuleConfig')
            ->willReturnCallback(static function (string $key) use ($moduleConfigValues): mixed {
                return $moduleConfigValues[$key] ?? null;
            });

        return $config;
    }
}
