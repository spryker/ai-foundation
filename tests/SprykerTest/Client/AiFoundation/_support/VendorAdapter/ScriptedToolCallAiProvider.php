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
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Providers\ToolMapperInterface;
use RuntimeException;

/**
 * Replays a scripted tool-call turn: every streamed round returns the tools of the corresponding
 * entry of the script, and once the script is exhausted it answers with the final text. Lets the
 * tool-call loop of the adapter — the pre/post tool call plugins and the tool result handling —
 * run for real against a deterministic vendor.
 */
class ScriptedToolCallAiProvider implements AIProviderInterface
{
    protected const string MESSAGE_ID = 'scripted-tool-call-message';

    protected const string ERROR_MESSAGE_NOT_SUPPORTED = 'The scripted provider only supports streaming.';

    protected const string ERROR_MESSAGE_FINAL_ROUND_FAILED = 'The scripted final round failed.';

    protected int $streamCount = 0;

    /**
     * @param array<int, array<int, \NeuronAI\Tools\ToolInterface>> $toolCallRounds
     */
    public function __construct(
        protected array $toolCallRounds = [],
        protected string $finalAnswer = '',
        protected ?Usage $toolCallUsage = null,
        protected ?Usage $finalUsage = null,
        protected bool $isFinalRoundFailing = false
    ) {
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        return $this;
    }

    /**
     * @param array<\NeuronAI\Tools\ToolInterface> $tools
     */
    public function setTools(array $tools): AIProviderInterface
    {
        return $this;
    }

    public function stream(Message ...$messages): Generator
    {
        $tools = $this->toolCallRounds[$this->streamCount] ?? null;
        $this->streamCount++;

        if ($tools !== null) {
            return $this->applyUsage(new ToolCallMessage(null, $tools), $this->toolCallUsage);
        }

        if ($this->isFinalRoundFailing) {
            throw new RuntimeException(static::ERROR_MESSAGE_FINAL_ROUND_FAILED);
        }

        yield new TextChunk(static::MESSAGE_ID, $this->finalAnswer);

        return $this->applyUsage(new AssistantMessage($this->finalAnswer), $this->finalUsage);
    }

    protected function applyUsage(Message $message, ?Usage $usage): Message
    {
        if ($usage === null) {
            return $message;
        }

        return $message->setUsage(new Usage($usage->inputTokens, $usage->outputTokens));
    }

    public function getStreamCount(): int
    {
        return $this->streamCount;
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
}
