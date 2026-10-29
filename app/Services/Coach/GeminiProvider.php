<?php

namespace App\Services\Coach;

use App\Exceptions\CoachRefusedException;
use App\Exceptions\CoachUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gemini generateContent API. Free-tier models (e.g. gemini-3.8-flash)
 * work without billing, within Google's free-tier rate limits.
 */
class GeminiProvider implements CoachProvider
{
    public const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    /** Statuses that mean the model is temporarily overloaded, worth one try on the fallback. */
    public const RETRYABLE_STATUSES = [500, 503];

    /** Finish reasons that mean Gemini stopped for safety or policy reasons. */
    public const SAFETY_FINISH_REASONS = ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'SPII'];

    public static function urlFor(string $model): string
    {
        return self::BASE_URL.'/'.rawurlencode($model).':generateContent';
    }

    public function answer(string $systemPrompt, string $question, ?CoachTools $tools = null): CoachAnswer
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new CoachUnavailableException('GEMINI_API_KEY is not set.');
        }

        $deadline = new CoachDeadline;
        $contents = [['role' => 'user', 'parts' => [['text' => $question]]]];
        $model = null;
        $usage = ['input' => 0, 'output' => 0, 'thinking' => 0];
        $toolCalls = [];
        $maxRounds = (int) config('services.coach.max_tool_rounds');

        for ($round = 0; ; $round++) {
            // After the last allowed tool round, the model must answer with what it has.
            $toolMode = $tools ? ($round < $maxRounds ? 'AUTO' : 'NONE') : null;
            $body = $this->body($systemPrompt, $contents, $toolMode);

            // Only the first request may switch to the fallback model: a
            // conversation that already has tool calls stays on one model.
            [$response, $model] = $model === null
                ? $this->sendWithFallback($key, $body, $deadline)
                : [$this->send($key, $model, $body, $deadline), $model];

            $this->throwIfFailed($response, $model);

            $usage['input'] += (int) $response->json('usageMetadata.promptTokenCount');
            $usage['output'] += (int) $response->json('usageMetadata.candidatesTokenCount');
            $usage['thinking'] += (int) $response->json('usageMetadata.thoughtsTokenCount');

            $content = $response->json('candidates.0.content') ?? [];
            $calls = collect($content['parts'] ?? [])->pluck('functionCall')->filter()->values();

            if ($toolMode === 'AUTO' && $calls->isNotEmpty()) {
                // Send the model's turn back exactly as received: Gemini 3 rejects
                // the next request if its thoughtSignature is missing or changed.
                // Decode from the raw body as objects: decoding to arrays turns
                // "args": {} into [], which Gemini rejects as a list.
                $contents[] = json_decode($response->body())->candidates[0]->content;
                $contents[] = [
                    'role' => 'user',
                    'parts' => $calls->map(function (array $call) use ($tools, &$toolCalls) {
                        $toolCalls[] = $call['name'];

                        return ['functionResponse' => array_filter([
                            'name' => $call['name'],
                            'id' => $call['id'] ?? null,
                            'response' => (object) $tools->run($call['name'], (array) ($call['args'] ?? [])),
                        ], fn ($value) => $value !== null)];
                    })->all(),
                ];

                continue;
            }

            $finishReason = $response->json('candidates.0.finishReason');

            // Skip "thought" parts some models return alongside the answer.
            $text = collect($content['parts'] ?? [])
                ->reject(fn ($part) => $part['thought'] ?? false)
                ->pluck('text')
                ->filter()
                ->implode("\n");

            if (trim($text) === '' && in_array($finishReason, self::SAFETY_FINISH_REASONS, true)) {
                Log::info('Coach (Gemini) withheld the answer', ['reason' => $finishReason]);

                throw new CoachRefusedException("Gemini withheld the answer: {$finishReason}.");
            }

            return new CoachAnswer(
                text: trim($text),
                provider: 'gemini',
                model: $response->json('modelVersion', $model),
                inputTokens: $usage['input'],
                outputTokens: $usage['output'],
                stopReason: $finishReason,
                thinkingTokens: $usage['thinking'],
                toolCalls: $toolCalls,
            );
        }
    }

    /**
     * @param  'AUTO'|'NONE'|null  $toolMode  null sends no tools at all.
     */
    private function body(string $systemPrompt, array $contents, ?string $toolMode): array
    {
        $generationConfig = ['maxOutputTokens' => config('services.coach.max_tokens')];

        if (filled($level = config('services.gemini.thinking_level'))) {
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => $level];
        }

        $body = [
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => $generationConfig,
        ];

        if ($toolMode !== null) {
            $body['tools'] = [['functionDeclarations' => array_map(
                // Gemini rejects an object schema with no properties, so leave it out.
                fn (array $tool) => empty((array) ($tool['parameters']['properties'] ?? []))
                    ? ['name' => $tool['name'], 'description' => $tool['description']]
                    : $tool,
                CoachTools::definitions(),
            )]];
            $body['toolConfig'] = ['functionCallingConfig' => ['mode' => $toolMode]];
        }

        return $body;
    }

    /**
     * @return array{0: Response, 1: string} the response and the model that produced it
     */
    private function sendWithFallback(string $key, array $body, CoachDeadline $deadline): array
    {
        $models = array_values(array_unique(array_filter([
            config('services.gemini.model'),
            config('services.gemini.fallback_model'),
        ])));

        foreach ($models as $attempt => $model) {
            $response = $this->send($key, $model, $body, $deadline);

            // 500/503 mean Google's model is overloaded right now: try the fallback model.
            if (in_array($response->status(), self::RETRYABLE_STATUSES, true) && $attempt < array_key_last($models)) {
                Log::warning('Coach (Gemini) model unavailable, trying fallback', [
                    'model' => $model,
                    'status' => $response->status(),
                ]);

                continue;
            }

            return [$response, $model];
        }
    }

    private function send(string $key, string $model, array $body, CoachDeadline $deadline): Response
    {
        try {
            // The key goes in a header, not the ?key= query string, so it never lands in URL logs.
            return Http::withHeaders(['x-goog-api-key' => $key])
                ->acceptJson()
                ->timeout($deadline->timeoutForNextCall())
                ->post(self::urlFor($model), $body);
        } catch (ConnectionException $e) {
            Log::warning('Coach (Gemini) failed to connect', ['model' => $model, 'error' => $e->getMessage()]);

            throw new CoachUnavailableException('Could not reach the Gemini API.', previous: $e);
        }
    }

    private function throwIfFailed(Response $response, string $model): void
    {
        if ($response->failed()) {
            // 429 here usually means the free-tier daily or per-minute quota is used up.
            Log::warning('Coach (Gemini) returned an error', [
                'model' => $model,
                'status' => $response->status(),
                'type' => $response->json('error.status'),
                // Gemini's explanation, e.g. which request field it rejected. Truncated; it never
                // contains the user's data, only details about the request format.
                'message' => mb_substr((string) $response->json('error.message'), 0, 500),
            ]);

            throw new CoachUnavailableException("Gemini API returned HTTP {$response->status()}.");
        }

        if ($blockReason = $response->json('promptFeedback.blockReason')) {
            Log::info('Coach (Gemini) blocked the question', ['reason' => $blockReason]);

            throw new CoachRefusedException("Gemini blocked the question: {$blockReason}.");
        }
    }
}
