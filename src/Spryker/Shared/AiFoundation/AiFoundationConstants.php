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
     * - Defines the message type for user messages in chat history.
     *
     * @api
     *
     * @var string
     */
    public const MESSAGE_TYPE_USER = 'user';

    /**
     * Specification:
     * - Defines the message type for assistant messages in chat history.
     *
     * @api
     *
     * @var string
     */
    public const MESSAGE_TYPE_ASSISTANT = 'assistant';

    /**
     * Specification:
     * - Defines the message type for tool call messages in chat history.
     * - Tool call messages represent AI requests to execute specific tools during conversation.
     * - Used when the AI model determines it needs to invoke a tool to complete the task.
     * - Contains tool name, arguments, and invocation metadata.
     * - Example: When AI needs to calculate "15 * 7", it creates a tool call message requesting calculator tool execution.
     *
     * @api
     *
     * @var string
     */
    public const MESSAGE_TYPE_TOOL_CALL = 'tool_call';

    /**
     * Specification:
     * - Defines the message type for tool result messages in chat history.
     * - Tool result messages contain the output returned after tool execution.
     * - Used to provide tool execution results back to the AI model for processing.
     * - Contains tool execution results, status, and any error information.
     * - Example: After calculator tool executes "15 * 7", the result "105" is returned in a tool result message.
     * - The AI model uses this result to continue the conversation and formulate the final response.
     *
     * @api
     *
     * @var string
     */
    public const MESSAGE_TYPE_TOOL_RESULT = 'tool_result';

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
     * - Defines the AI configuration key for the conversation history.
     *
     * @api
     *
     * @var string
     */
    public const AI_CONVERSATION_HISTORY_CONFIG = 'conversation_history';

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
     * - Returns the maximum context window size for conversation history.
     * - Value is expressed in tokens.
     * - Represents the maximum number of tokens to retain in conversation history storage per conversation.
     * - When the conversation history exceeds this limit, the oldest messages are automatically pruned to stay within the limit.
     * - This limit is applied per conversation, not globally across all conversations.
     * - Helps prevent storage overflow and ensures conversation history fits within AI model context limits.
     *
     * @api
     *
     * @var string
     */
    public const CONVERSATION_HISTORY_CONTEXT_WINDOW = 'CONVERSATION_HISTORY_CONTEXT_WINDOW';

    /**
     * Specification:
     * - Defines the state machine name used for AI workflow processing.
     * - Used to identify and route items through the AI workflow state machine.
     *
     * @api
     *
     * @var string
     */
    public const AI_WORKFLOW_STATE_MACHINE_NAME = 'AiWorkflow';

    /**
     * Specification:
     * - Defines the audit logger channel name for AI interaction logging.
     *
     * @api
     */
    public const string AUDIT_LOGGER_CHANNEL_NAME_AI_INTERACTION = 'ai_foundation:ai_interaction';

    /**
     * Specification:
     * - Defines the audit log context key that holds the prepared AiInteractionLogTransfer for persistence.
     *
     * @api
     */
    public const string AUDIT_LOG_CONTEXT_KEY_TRANSFER = 'ai_interaction_log';

    /**
     * Specification:
     * - Defines the prefix pattern used to identify configuration references in AI configuration values.
     * - Values prefixed with this string are resolved at runtime via `\Spryker\Zed\Configuration\Business\ConfigurationFacade::getConfigurationValue()`.
     *
     * @api
     */
    public const string CONFIGURATION_REFERENCE_PREFIX = 'configuration::';
}
