<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\AiFoundation;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface AiFoundationConstants
{
    /**
     * Specification:
     * - Defines the provider name for Anthropic AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_ANTHROPIC = 'anthropic';

    /**
     * Specification:
     * - Defines the provider name for OpenAI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_OPENAI = 'openai';

    /**
     * Specification:
     * - Defines the provider name for AWS Bedrock Runtime service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_BEDROCK = 'bedrock';

    /**
     * Specification:
     * - Defines the provider name for Deepseek AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_DEEPSEEK = 'deepseek';

    /**
     * Specification:
     * - Defines the provider name for Google Gemini AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_GEMINI = 'gemini';

    /**
     * Specification:
     * - Defines the provider name for HuggingFace AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_HUGGINGFACE = 'huggingface';

    /**
     * Specification:
     * - Defines the provider name for Mistral AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_MISTRAL = 'mistral';

    /**
     * Specification:
     * - Defines the provider name for Ollama AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_OLLAMA = 'ollama';

    /**
     * Specification:
     * - Defines the provider name for XAI Grok service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_GROK = 'grok';

    /**
     * Specification:
     * - Defines the provider name for Azure Open AI service.
     *
     * @api
     *
     * @var string
     */
    public const PROVIDER_AZURE_OPEN_AI = 'azureopenai';

    /**
     * Specification:
     * - Defines the attachment type for document attachments.
     *
     * @api
     *
     * @var string
     */
    public const ATTACHMENT_TYPE_DOCUMENT = 'document';

    /**
     * Specification:
     * - Defines the attachment type for image attachments.
     *
     * @api
     *
     * @var string
     */
    public const ATTACHMENT_TYPE_IMAGE = 'image';

    /**
     * Specification:
     * - Defines the attachment content type for URL-based content.
     *
     * @api
     *
     * @var string
     */
    public const ATTACHMENT_CONTENT_TYPE_URL = 'url';

    /**
     * Specification:
     * - Defines the attachment content type for Base64-encoded content.
     *
     * @api
     *
     * @var string
     */
    public const ATTACHMENT_CONTENT_TYPE_BASE64 = 'base64';

    /**
     * Specification:
     * - Defines the configuration key for the list of available AI configurations.
     *
     * @api
     *
     * @var string
     */
    public const AI_CONFIGURATIONS = 'AI_CONFIGURATIONS';

    /**
     * Specification:
     * - Defines the conventional configuration key for the default AI configuration.
     *
     * @api
     *
     * @var string
     */
    public const AI_CONFIGURATION_DEFAULT = 'AI_CONFIGURATION_DEFAULT';

    /**
     * Specification:
     * - Defines the AI configuration key for the provider name.
     *
     * @api
     *
     * @var string
     */
    public const AI_PROVIDER_NAME = 'provider_name';

    /**
     * Specification:
     * - Defines the AI configuration key for the provider configuration.
     *
     * @api
     *
     * @var string
     */
    public const AI_PROVIDER_CONFIG = 'provider_config';

    /**
     * Specification:
     * - Defines the AI configuration key for the system prompt.
     *
     * @api
     *
     * @var string
     */
    public const AI_CONFIG_SYSTEM_PROMPT = 'system_prompt';

    /**
     * Specification:
     * - Defines the AiWorkflow state machine name.
     *
     * @api
     *
     * @var string
     */
    public const AI_WORKFLOW_STATE_MACHINE_NAME = 'AiWorkflow';
}
