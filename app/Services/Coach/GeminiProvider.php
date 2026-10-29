<?php

namespace App\Services\Coach;

use App\Exceptions\CoachRefusedException;
use App\Exceptions\CoachUnavailableException;
use Illuminate\Http\Client\ConnectionException;
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
    public const SAFETY_FINISH_REASONS = ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'SPII'];

    public static function urlFor(string $model): string
    {
        return self::BASE_URL . '/' . rawurlencode($model) . ':generateContent';
    }

    private function generationConfig(): array
    {
        $config = ['maxOutputTokens' => config('services.coach.max_tokens')];

        if (filled($level = config('services.gemini.thinking_level'))) {
            $config['thinkingConfig'] = ['thinkingLevel' => $level];
        }

        return $config;
    }

    public function answer(string $systemPrompt, string $question): CoachAnswer
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new CoachUnavailableException('GEMINI_API_KEY is not set.');
        }

        $model = config('services.gemini.model');

        try {
            // The key goes in a header, not the ?key= query string, so it never lands in URL logs.
            $response = Http::withHeaders(['x-goog-api-key' => $key])
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
        } catch (ConnectionException $e) {
            Log::warning('Coach (Gemini) failed to connect', ['error' => $e->getMessage()]);

            throw new CoachUnavailableException('Could not reach the Gemini API.', previous: $e);
        }

        if ($response->failed()) {
            // 429 here usually means the free-tier daily or per-minute quota is used up.
            Log::warning('Coach (Gemini) returned an error', [
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
