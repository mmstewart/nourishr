<?php

namespace App\Services\Coach;

use App\Exceptions\CoachUnavailableException;

/**
 * A model API that can answer a coach question. Each provider translates
 * the same system prompt, question, and tools into its own request format,
 * and runs the tool loop: the model asks for tools, gets their results, and
 * continues until it answers.
 */
interface CoachProvider
{
    /**
     * @throws CoachUnavailableException
     */
    public function answer(string $systemPrompt, string $question, ?CoachTools $tools = null): CoachAnswer;
}
