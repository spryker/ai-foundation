<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Client\AiFoundation\VendorAdapter;

use Generator;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Providers\ToolMapperInterface;
use RuntimeException;

/**
 * Stands in for the AI vendor, the only third party a streaming turn reaches. It replays a scripted
 * token stream so everything around it — the chat history, the stream mapper, the stream event
 * plugins and the audit log — runs for real.
 */
class ScriptedAiProvider implements AIProviderInterface
{
    protected const string MESSAGE_ID = 'scripted-message';

    protected const string ERROR_MESSAGE_NOT_SUPPORTED = 'The scripted provider only supports streaming.';

    /**
     * @var array<int, \NeuronAI\Chat\Messages\Message>
     */
    protected array $receivedMessages = [];

    /**
     * @var array<int, \NeuronAI\Tools\ToolInterface>
     */
    protected array $registeredTools = [];

    protected ?string $systemPrompt = null;

    /**
     * @param array<int, string> $tokens
     */
    public function __construct(protected array $tokens = [])
    {
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->systemPrompt = $prompt;

        return $this;
    }

    /**
     * @param array<\NeuronAI\Tools\ToolInterface> $tools
     */
    public function setTools(array $tools): AIProviderInterface
    {
        $this->registeredTools = $tools;

        return $this;
    }

    public function stream(Message ...$messages): Generator
    {
        $this->receivedMessages = array_values($messages);

        foreach ($this->tokens as $token) {
            yield new TextChunk(static::MESSAGE_ID, $token);
        }

        return new AssistantMessage(implode('', $this->tokens));
    }

    public function chat(Message ...$messages): Message
    {
        throw new RuntimeException(static::ERROR_MESSAGE_NOT_SUPPORTED);
    }

    /**
     * @param \NeuronAI\Chat\Messages\Message|array<\NeuronAI\Chat\Messages\Message> $messages
     * @param array<string, mixed> $response_schema
     *
     * @throws \RuntimeException
     */
    public function structured(array|Message $messages, string $class, array $response_schema): Message
    {
        throw new RuntimeException(static::ERROR_MESSAGE_NOT_SUPPORTED);
    }

    public function messageMapper(): MessageMapperInterface
    {
        throw new RuntimeException(static::ERROR_MESSAGE_NOT_SUPPORTED);
    }

    public function toolPayloadMapper(): ToolMapperInterface
    {
        throw new RuntimeException(static::ERROR_MESSAGE_NOT_SUPPORTED);
    }

    public function setHttpClient(HttpClientInterface $client): AIProviderInterface
    {
        return $this;
    }

    /**
     * @return array<int, \NeuronAI\Chat\Messages\Message>
     */
    public function getReceivedMessages(): array
    {
        return $this->receivedMessages;
    }

    /**
     * @return array<int, \NeuronAI\Tools\ToolInterface>
     */
    public function getRegisteredTools(): array
    {
        return $this->registeredTools;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }
}
