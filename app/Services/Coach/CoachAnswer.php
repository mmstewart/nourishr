<?php

namespace App\Services\Coach;

/**
 * A provider-neutral answer, so the rest of the app never touches
 * Claude- or Gemini-specific response shapes.
 */
final readonly class CoachAnswer
{
    public function __construct(
        public string $text,
        public string $provider,
        public ?string $model = null,
        public ?int $inputTokens = null,
        public ?int $outputTokens = null,
        public ?string $stopReason = null,
        public ?int $thinkingTokens = null,
        /** @var list<string> names of the tools called, in order */
        public array $toolCalls = [],
    ) {}
}
