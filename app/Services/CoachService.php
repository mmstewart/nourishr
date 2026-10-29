<?php

namespace App\Services;

use App\Exceptions\CoachUnavailableException;
use App\Services\Coach\ClaudeProvider;
use App\Services\Coach\CoachProvider;
use App\Services\Coach\GeminiProvider;
use Illuminate\Support\Facades\Log;

/**
 * Answers a user's nutrition question through whichever model API
 * COACH_PROVIDER selects (gemini or anthropic).
 *
 * Phase 1: plain question in, plain answer out. Tool calling (Phase 2) and
 * retrieval over nutrition guidelines (Phase 3) build on this class.
 */
class CoachService
{
    public const PROVIDERS = [
        'gemini' => GeminiProvider::class,
        'anthropic' => ClaudeProvider::class,
    ];

    public const SYSTEM_PROMPT = <<<'PROMPT'
        You are the Nourishr nutrition coach. Nourishr is an app where people log their meals, calories,
        protein, carbohydrates, fats, and weight while working toward a weight goal.

        Answer nutrition and healthy-eating questions clearly and briefly (under 200 words), with practical,
        specific suggestions. Reply in plain text without Markdown; use short lines starting with "- " for lists.
        You give general nutrition information, not medical advice: if a question involves a medical condition,
        medication, pregnancy, or an eating disorder, recommend talking with a doctor or registered dietitian.
        Politely decline questions unrelated to nutrition, food, or fitness.
        PROMPT;

    public function ask(string $question): string
    {
        $answer = $this->provider()->answer(self::SYSTEM_PROMPT, $question);

        if ($answer->text === '') {
            throw new CoachUnavailableException("{$answer->provider} returned no text (stop reason: {$answer->stopReason}).");
        }

        Log::info('Coach answered', [
            'provider' => $answer->provider,
            'model' => $answer->model,
            'input_tokens' => $answer->inputTokens,
            'output_tokens' => $answer->outputTokens,
            'stop_reason' => $answer->stopReason,
        ]);

        return $answer->text;
    }

    public function provider(): CoachProvider
    {
        $name = config('services.coach.provider');
        $class = self::PROVIDERS[$name] ?? null;

        if ($class === null) {
            throw new CoachUnavailableException("Unknown COACH_PROVIDER \"{$name}\".");
        }

        return app($class);
    }
}
