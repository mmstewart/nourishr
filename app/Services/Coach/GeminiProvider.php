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

    /** Finish reasons that mean Gemini stopped for safety or policy reasons. */
    /** Statuses that mean the model is temporarily overloaded, worth one try on the fallback. */
    public const RETRYABLE_STATUSES = [500, 503];

    public const SAFETY_FINISH_REASONS = ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'SPII'];

    public static function urlFor(string $model): string
    {
        return self::BASE_URL.'/'.rawurlencode($model).':generateContent';
    }

    private function generationConfig(): array
    {
        $config = ['maxOutputTokens' => config('services.coach.max_tokens')];

        if (filled($level = config('services.gemini.thinking_level'))) {
            $config['thinkingConfig'] = ['thinkingLevel' => $level];
        }

        return $config;
    }

    private function send(string $key, string $model, string $systemPrompt, string $question): Response
    {
        // The key goes in a header, not the ?key= query string, so it never lands in URL logs.
        return Http::withHeaders(['x-goog-api-key' => $key])
            ->acceptJson()
            ->timeout(config('services.coach.timeout'))
            ->post(self::urlFor($model), [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $question]]],
                ],
                'generationConfig' => $this->generationConfig(),
            ]);
    }

    public function answer(string $systemPrompt, string $question): CoachAnswer
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new CoachUnavailableException('GEMINI_API_KEY is not set.');
        }

        $models = array_values(array_unique(array_filter([
            config('services.gemini.model'),
            config('services.gemini.fallback_model'),
        ])));

        foreach ($models as $attempt => $model) {
            $isLastAttempt = $attempt === array_key_last($models);

            try {
                $response = $this->send($key, $model, $systemPrompt, $question);
            } catch (ConnectionException $e) {
                Log::warning('Coach (Gemini) failed to connect', ['model' => $model, 'error' => $e->getMessage()]);

                throw new CoachUnavailableException('Could not reach the Gemini API.', previous: $e);
            }

            // 500/503 mean Google's model is overloaded right now: try the fallback model.
            if (in_array($response->status(), self::RETRYABLE_STATUSES, true) && ! $isLastAttempt) {
                Log::warning('Coach (Gemini) model unavailable, trying fallback', [
                    'model' => $model,
                    'status' => $response->status(),
                ]);

                continue;
            }

            break;
        }

        if ($response->failed()) {
            // 429 here usually means the free-tier daily or per-minute quota is used up.
            Log::warning('Coach (Gemini) returned an error', [
                'model' => $model,
                'status' => $response->status(),
                'type' => $response->json('error.status'),
            ]);

            throw new CoachUnavailableException("Gemini API returned HTTP {$response->status()}.");
        }

        if ($blockReason = $response->json('promptFeedback.blockReason')) {
            Log::info('Coach (Gemini) blocked the question', ['reason' => $blockReason]);

            throw new CoachRefusedException("Gemini blocked the question: {$blockReason}.");
        }

        $finishReason = $response->json('candidates.0.finishReason');

        // Skip "thought" parts some models return alongside the answer.
        $text = collect($response->json('candidates.0.content.parts', []))
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
            inputTokens: $response->json('usageMetadata.promptTokenCount'),
            outputTokens: $response->json('usageMetadata.candidatesTokenCount'),
            stopReason: $finishReason,
            thinkingTokens: $response->json('usageMetadata.thoughtsTokenCount'),
        );
    }
}
