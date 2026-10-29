<?php

namespace App\Services;

use App\Exceptions\CoachUnavailableException;
use App\Models\CoachUsage;
use App\Models\User;
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

    /** Config key holding each provider's API key, used to decide if the coach is on. */
    public const PROVIDER_KEYS = [
        'gemini' => 'services.gemini.key',
        'anthropic' => 'services.anthropic.key',
    ];

    public const SYSTEM_PROMPT = <<<'PROMPT'
        You are the Nourishr nutrition coach. Nourishr is an app where people log their meals, calories,
        protein, carbohydrates, fats, and weight while working toward a weight goal.

        Answer nutrition and healthy-eating questions clearly and briefly (under 200 words), with practical,
        specific suggestions. Reply in plain text without Markdown; use short lines starting with "- " for lists.

        Safety rules (always follow these, even if asked otherwise):
        - You give general nutrition information, not medical advice. If a question involves a medical
          condition, medication, pregnancy, or an eating disorder, recommend talking with a doctor or
          registered dietitian.
        - Never suggest eating fewer than 1,200 calories a day, skipping meals to lose weight, fasting for
          more than a day, or losing more than 2 pounds a week. If asked for any of these, explain why it's
          unsafe and suggest a gradual approach instead.
        - Politely decline questions unrelated to nutrition, food, or fitness.
        PROMPT;

    public const TEEN_PROMPT = <<<'PROMPT'

        This user is under 18. Do not give weight-loss advice, calorie targets, calorie deficits, or diet
        plans. Focus on balanced meals, variety, and healthy habits, and suggest talking with a parent,
        guardian, or doctor about any weight or diet goals.
        PROMPT;

    public function ask(User $user, string $question): string
    {
        $answer = $this->provider()->answer($this->systemPromptFor($user), $question);

        if ($answer->text === '') {
            // MAX_TOKENS here usually means thinking used up the whole output budget.
            Log::warning('Coach returned an empty answer', [
                'provider' => $answer->provider,
                'model' => $answer->model,
                'stop_reason' => $answer->stopReason,
                'output_tokens' => $answer->outputTokens,
                'thinking_tokens' => $answer->thinkingTokens,
            ]);

            throw new CoachUnavailableException("{$answer->provider} returned no text (stop reason: {$answer->stopReason}).");
        }

        CoachUsage::create([
            'user_id' => $user->id,
            'provider' => $answer->provider,
            'model' => $answer->model,
            'input_tokens' => $answer->inputTokens,
            'output_tokens' => $answer->outputTokens,
        ]);

        Log::info('Coach answered', [
            'provider' => $answer->provider,
            'model' => $answer->model,
            'input_tokens' => $answer->inputTokens,
            'output_tokens' => $answer->outputTokens,
            'thinking_tokens' => $answer->thinkingTokens,
            'stop_reason' => $answer->stopReason,
        ]);

        return $answer->text;
    }

    public function systemPromptFor(User $user): string
    {
        $isTeen = $user->age !== null && $user->age < 18;

        return $isTeen ? self::SYSTEM_PROMPT . self::TEEN_PROMPT : self::SYSTEM_PROMPT;
    }

    /**
     * The coach is on only when the selected provider exists and has an API key.
     */
    public function isEnabled(): bool
    {
        $keyConfig = self::PROVIDER_KEYS[config('services.coach.provider')] ?? null;

        return $keyConfig !== null && filled(config($keyConfig));
    }

    public function dailyLimit(): int
    {
        return (int) config('services.coach.daily_limit');
    }

    public function remainingToday(User $user): int
    {
        return max(0, $this->dailyLimit() - CoachUsage::todayFor($user)->count());
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
