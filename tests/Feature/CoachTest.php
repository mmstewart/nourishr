<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Coach\ClaudeProvider;
use App\Services\Coach\GeminiProvider;
use App\Services\CoachService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachTest extends TestCase
{
    use RefreshDatabase;

    private const GEMINI_MODEL = 'gemini-test-model';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.coach.provider' => 'gemini',
            'services.gemini.key' => 'gemini-test-key',
            'services.gemini.model' => self::GEMINI_MODEL,
            'services.anthropic.key' => 'claude-test-key',
            'services.anthropic.model' => 'claude-test-model',
        ]);
    }

    private function completedUser(): User
    {
        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        return $user;
    }

    private function geminiUrl(): string
    {
        return GeminiProvider::urlFor(self::GEMINI_MODEL);
    }

    private function fakeGeminiAnswer(string $text): void
    {
        Http::fake([
            $this->geminiUrl() => Http::response([
                'candidates' => [[
                    'content' => ['role' => 'model', 'parts' => [['text' => $text]]],
                    'finishReason' => 'STOP',
                ]],
                'usageMetadata' => ['promptTokenCount' => 120, 'candidatesTokenCount' => 40],
                'modelVersion' => self::GEMINI_MODEL,
            ]),
        ]);
    }

    private function fakeClaudeAnswer(string $text): void
    {
        Http::fake([
            ClaudeProvider::API_URL => Http::response([
                'id' => 'msg_test',
                'type' => 'message',
                'role' => 'assistant',
                'model' => 'claude-test-model',
                'content' => [['type' => 'text', 'text' => $text]],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 120, 'output_tokens' => 40],
            ]),
        ]);
    }

    private function ask(string $question = 'What is a high-protein breakfast?')
    {
        return $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => $question]);
    }

    // --- Page and access -------------------------------------------------

    public function test_coach_screen_can_be_rendered()
    {
        $this->actingAs($this->completedUser())
            ->get(route('coach'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Coach'));
    }

    public function test_guests_cannot_use_the_coach()
    {
        Http::fake();

        $this->get(route('coach'))->assertRedirect(route('login'));
        $this->post(route('coach.ask'), ['question' => 'Is rice healthy?'])->assertRedirect(route('login'));

        Http::assertNothingSent();
    }

    // --- Gemini (default provider) ---------------------------------------

    public function test_user_gets_an_answer_from_gemini()
    {
        $this->fakeGeminiAnswer('Try Greek yogurt with berries.');

        $this->ask()
            ->assertRedirect(route('coach'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('coach_answer', 'Try Greek yogurt with berries.')
            ->assertSessionHas('coach_question', 'What is a high-protein breakfast?');
    }

    public function test_answer_is_shown_on_the_coach_page()
    {
        $this->fakeGeminiAnswer('Try Greek yogurt with berries.');

        $this->actingAs($this->completedUser())
            ->followingRedirects()
            ->post(route('coach.ask'), ['question' => 'What is a high-protein breakfast?'])
            ->assertInertia(fn ($page) => $page
                ->component('Coach')
                ->where('question', 'What is a high-protein breakfast?')
                ->where('answer', 'Try Greek yogurt with berries.'));
    }

    public function test_gemini_request_sends_key_in_header_and_prompt_in_body()
    {
        $this->fakeGeminiAnswer('Answer');

        $this->ask('How much water should I drink?');

        Http::assertSent(function (Request $request) {
            return $request->url() === $this->geminiUrl()
                && $request->hasHeader('x-goog-api-key', 'gemini-test-key')
                && ! str_contains($request->url(), 'key=')
                && $request['systemInstruction']['parts'][0]['text'] === CoachService::SYSTEM_PROMPT
                && $request['contents'] === [['role' => 'user', 'parts' => [['text' => 'How much water should I drink?']]]]
                && $request['generationConfig']['maxOutputTokens'] === config('services.coach.max_tokens');
        });
    }

    public function test_gemini_thought_parts_are_left_out_of_the_answer()
    {
        Http::fake([
            $this->geminiUrl() => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [
                        ['text' => 'Internal reasoning', 'thought' => true],
                        ['text' => 'Eat more beans.'],
                    ]],
                    'finishReason' => 'STOP',
                ]],
            ]),
        ]);

        $this->ask()->assertSessionHas('coach_answer', 'Eat more beans.');
    }

    public function test_gemini_quota_error_shows_a_friendly_message()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['error' => ['code' => 429, 'status' => 'RESOURCE_EXHAUSTED']], 429),
        ]);

        $this->ask()
            ->assertRedirect(route('coach'))
            ->assertSessionHas('error')
            ->assertSessionMissing('coach_answer');
    }

    public function test_gemini_blocked_question_shows_a_friendly_message()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']]),
        ]);

        $this->ask()->assertSessionHas('error')->assertSessionMissing('coach_answer');
    }

    public function test_gemini_answer_cut_off_with_no_text_is_treated_as_unavailable()
    {
        Http::fake([
            $this->geminiUrl() => Http::response([
                'candidates' => [['content' => ['parts' => []], 'finishReason' => 'MAX_TOKENS']],
            ]),
        ]);

        $this->ask()->assertSessionHas('error')->assertSessionMissing('coach_answer');
    }

    public function test_missing_gemini_key_does_not_call_the_api()
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->ask()->assertSessionHas('error');

        Http::assertNothingSent();
    }

    // --- Claude (paid provider) -------------------------------------------

    public function test_user_gets_an_answer_from_claude_when_selected()
    {
        config(['services.coach.provider' => 'anthropic']);
        $this->fakeClaudeAnswer('Try Greek yogurt with berries.');

        $this->ask()->assertSessionHas('coach_answer', 'Try Greek yogurt with berries.');
    }

    public function test_claude_request_sends_the_question_model_and_api_headers()
    {
        config(['services.coach.provider' => 'anthropic']);
        $this->fakeClaudeAnswer('Answer');

        $this->ask('How much water should I drink?');

        Http::assertSent(function (Request $request) {
            return $request->url() === ClaudeProvider::API_URL
                && $request->hasHeader('x-api-key', 'claude-test-key')
                && $request->hasHeader('anthropic-version', ClaudeProvider::API_VERSION)
                && $request['model'] === 'claude-test-model'
                && $request['system'] === CoachService::SYSTEM_PROMPT
                && $request['messages'] === [['role' => 'user', 'content' => 'How much water should I drink?']];
        });
    }

    public function test_claude_api_error_shows_a_friendly_message()
    {
        config(['services.coach.provider' => 'anthropic']);
        Http::fake([
            ClaudeProvider::API_URL => Http::response(['type' => 'error', 'error' => ['type' => 'overloaded_error']], 529),
        ]);

        $this->ask()->assertSessionHas('error')->assertSessionMissing('coach_answer');
    }

    public function test_claude_empty_answer_is_treated_as_unavailable()
    {
        config(['services.coach.provider' => 'anthropic']);
        Http::fake([
            ClaudeProvider::API_URL => Http::response(['content' => [], 'stop_reason' => 'end_turn']),
        ]);

        $this->ask()->assertSessionHas('error')->assertSessionMissing('coach_answer');
    }

    public function test_unknown_provider_shows_a_friendly_message()
    {
        config(['services.coach.provider' => 'not-a-provider']);
        Http::fake();

        $this->ask()->assertSessionHas('error');

        Http::assertNothingSent();
    }

    // --- Validation and limits ---------------------------------------------

    public function test_question_is_required_and_limited_in_length()
    {
        Http::fake();
        $user = $this->completedUser();

        $this->actingAs($user)->post(route('coach.ask'), ['question' => ''])
            ->assertSessionHasErrors('question');

        $this->actingAs($user)->post(route('coach.ask'), ['question' => str_repeat('a', 501)])
            ->assertSessionHasErrors('question');

        Http::assertNothingSent();
    }

    public function test_coach_throttles_excessive_requests()
    {
        $this->fakeGeminiAnswer('Answer');
        $user = $this->completedUser();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('coach.ask'), ['question' => 'Is rice healthy?']);
        }

        $this->actingAs($user)
            ->post(route('coach.ask'), ['question' => 'Is rice healthy?'])
            ->assertStatus(429);
    }
}
