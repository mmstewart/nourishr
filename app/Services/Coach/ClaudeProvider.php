<?php

namespace App\Services\Coach;

use App\Exceptions\CoachUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Claude Messages API (paid, needs API credits).
 */
class ClaudeProvider implements CoachProvider
{
    public const API_URL = 'https://api.anthropic.com/v1/messages';

    public const API_VERSION = '2023-06-01';

    public function answer(string $systemPrompt, string $question): CoachAnswer
    {
        $key = config('services.anthropic.key');

        if (blank($key)) {
            throw new CoachUnavailableException('ANTHROPIC_API_KEY is not set.');
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => self::API_VERSION,
            ])
                ->acceptJson()
                ->timeout(config('services.coach.timeout'))
                ->post(self::API_URL, [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => config('services.coach.max_tokens'),
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $question],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Coach (Claude) failed to connect', ['error' => $e->getMessage()]);

            throw new CoachUnavailableException('Could not reach the Claude API.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Coach (Claude) returned an error', [
                'status' => $response->status(),
                'type' => $response->json('error.type'),
            ]);

            throw new CoachUnavailableException("Claude API returned HTTP {$response->status()}.");
        }

        $text = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        return new CoachAnswer(
            text: trim($text),
            provider: 'anthropic',
            model: $response->json('model'),
            inputTokens: $response->json('usage.input_tokens'),
            outputTokens: $response->json('usage.output_tokens'),
            stopReason: $response->json('stop_reason'),
        );
    }
}
