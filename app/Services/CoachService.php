<?php

namespace App\Services;

use App\Exceptions\CoachUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a user's question to the Claude Messages API and returns the answer text.
 *
 * Phase 1: plain question in, plain answer out. Tool calling (Phase 2) and
 * retrieval over nutrition guidelines (Phase 3) build on this class.
 */
class CoachService
{
    public const API_URL = 'https://api.anthropic.com/v1/messages';

    public const API_VERSION = '2023-06-01';

    public const SYSTEM_PROMPT = <<<'PROMPT'
        You are the Nourishr nutrition coach. Nourishr is an app where people log their meals, calories,
        protein, carbohydrates, fats, and weight while working toward a weight goal.

        Answer nutrition and healthy-eating questions clearly and briefly (under 200 words), with practical,
        specific suggestions. Reply in plain text without Markdown; use short lines starting with "- " for lists. You give general nutrition information, not medical advice: if a question involves
        a medical condition, medication, pregnancy, or an eating disorder, recommend talking with a doctor or
        registered dietitian. Politely decline questions unrelated to nutrition, food, or fitness.
        PROMPT;

    public function ask(string $question): string
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
                ->timeout(config('services.anthropic.timeout'))
                ->post(self::API_URL, [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => config('services.anthropic.max_tokens'),
                    'system' => self::SYSTEM_PROMPT,
                    'messages' => [
                        ['role' => 'user', 'content' => $question],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Coach request failed to connect', ['error' => $e->getMessage()]);

            throw new CoachUnavailableException('Could not reach the Claude API.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Coach request returned an error', [
                'status' => $response->status(),
                'type' => $response->json('error.type'),
            ]);

            throw new CoachUnavailableException("Claude API returned HTTP {$response->status()}.");
        }

        $answer = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        if (trim($answer) === '') {
            throw new CoachUnavailableException('Claude API returned no text.');
        }

        Log::info('Coach answered', [
            'model' => $response->json('model'),
            'input_tokens' => $response->json('usage.input_tokens'),
            'output_tokens' => $response->json('usage.output_tokens'),
            'stop_reason' => $response->json('stop_reason'),
        ]);

        return trim($answer);
    }
}
