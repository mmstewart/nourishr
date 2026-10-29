<?php

namespace App\Services\Coach;

/**
 * A model API that can answer a coach question. Each provider translates
 * the same system prompt and question into its own request format.
 */
interface CoachProvider
{
    /**
     * @throws \App\Exceptions\CoachUnavailableException
     */
    public function answer(string $systemPrompt, string $question): CoachAnswer;
}
