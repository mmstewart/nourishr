<?php

namespace App\Services\Coach;

use App\Exceptions\CoachRefusedException;
use App\Exceptions\CoachUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Claude Messages API (paid, needs API credits).
 */
class ClaudeProvider implements CoachProvider
{
    public const API_URL = 'https://api.anthropic.com/v1/messages';

    public const API_VERSION = '2023-06-01';

    public function answer(string $systemPrompt, string $question, ?CoachTools $tools = null): CoachAnswer
    {
        $key = config('services.anthropic.key');

        if (blank($key)) {
            throw new CoachUnavailableException('ANTHROPIC_API_KEY is not set.');
        }

        $deadline = new CoachDeadline;
        $messages = [['role' => 'user', 'content' => $question]];
        $usage = ['input' => 0, 'output' => 0];
        $toolCalls = [];
        $maxRounds = (int) config('services.coach.max_tool_rounds');

        for ($round = 0; ; $round++) {
            // After the last allowed tool round, Claude must answer with what it has.
            $toolChoice = $tools ? ($round < $maxRounds ? 'auto' : 'none') : null;

            $response = $this->send($key, $this->body($systemPrompt, $messages, $toolChoice), $deadline);

            $usage['input'] += (int) $response->json('usage.input_tokens');
            $usage['output'] += (int) $response->json('usage.output_tokens');

            $content = $response->json('content', []);
            $stopReason = $response->json('stop_reason');

            if ($stopReason === 'refusal') {
                Log::info('Coach (Claude) declined the question');

                throw new CoachRefusedException('Claude declined the question.');
            }

            $calls = collect($content)->where('type', 'tool_use')->values();

            if ($toolChoice === 'auto' && $stopReason === 'tool_use' && $calls->isNotEmpty()) {
                $messages[] = ['role' => 'assistant', 'content' => $content];
                $messages[] = [
                    'role' => 'user',
                    'content' => $calls->map(function (array $call) use ($tools, &$toolCalls) {
                        $toolCalls[] = $call['name'];

                        return [
                            'type' => 'tool_result',
                            'tool_use_id' => $call['id'],
                            'content' => json_encode($tools->run($call['name'], (array) ($call['input'] ?? []))),
                        ];
                    })->all(),
                ];

                continue;
            }

            $text = collect($content)->where('type', 'text')->pluck('text')->implode("\n");

            return new CoachAnswer(
                text: trim($text),
                provider: 'anthropic',
                model: $response->json('model'),
                inputTokens: $usage['input'],
                outputTokens: $usage['output'],
                stopReason: $stopReason,
                toolCalls: $toolCalls,
            );
        }
    }

    /**
     * @param  'auto'|'none'|null  $toolChoice  null sends no tools at all.
     */
    private function body(string $systemPrompt, array $messages, ?string $toolChoice): array
    {
        $body = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => config('services.coach.max_tokens'),
            'system' => $systemPrompt,
            'messages' => $messages,
        ];

        if ($toolChoice !== null) {
            $body['tools'] = array_map(fn (array $tool) => [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'input_schema' => $tool['parameters'],
            ], CoachTools::definitions());
            $body['tool_choice'] = ['type' => $toolChoice];
        }

        return $body;
    }

    private function send(string $key, array $body, CoachDeadline $deadline): Response
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => self::API_VERSION,
            ])
                ->acceptJson()
                ->timeout($deadline->timeoutForNextCall())
                ->post(self::API_URL, $body);
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

        return $response;
    }
}
