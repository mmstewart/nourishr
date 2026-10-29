<?php

namespace Tests\Feature;

use App\Models\CoachUsage;
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

    private const GEMINI_FALLBACK_MODEL = 'gemini-fallback-model';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.coach.provider' => 'gemini',
            'services.gemini.key' => 'gemini-test-key',
            'services.gemini.model' => self::GEMINI_MODEL,
            'services.gemini.fallback_model' => self::GEMINI_FALLBACK_MODEL,
            'services.anthropic.key' => 'claude-test-key',
            'services.anthropic.model' => 'claude-test-model',
        ]);
    }

    private function completedUser(array $attributes = []): User
    {
        $user = User::factory()->completed()->create($attributes);

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
                && $request['generationConfig']['maxOutputTokens'] === config('services.coach.max_tokens')
                && $request['generationConfig']['thinkingConfig'] === ['thinkingLevel' => 'low'];
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

    private function geminiResponse(string $text, string $model): array
    {
        return [
            'candidates' => [['content' => ['parts' => [['text' => $text]]], 'finishReason' => 'STOP']],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 30],
            'modelVersion' => $model,
        ];
    }

    public function test_overloaded_model_falls_back_to_the_lighter_model()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['error' => ['code' => 503, 'status' => 'UNAVAILABLE']], 503),
            GeminiProvider::urlFor(self::GEMINI_FALLBACK_MODEL) => Http::response(
                $this->geminiResponse('Fallback answer', self::GEMINI_FALLBACK_MODEL)
            ),
        ]);

        $this->ask()->assertSessionHas('coach_answer', 'Fallback answer');

        Http::assertSentCount(2);
        $this->assertSame(self::GEMINI_FALLBACK_MODEL, CoachUsage::sole()->model);
    }

    public function test_coach_is_unavailable_when_both_models_are_overloaded()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['error' => ['status' => 'UNAVAILABLE']], 503),
            GeminiProvider::urlFor(self::GEMINI_FALLBACK_MODEL) => Http::response(['error' => ['status' => 'UNAVAILABLE']], 503),
        ]);

        $this->ask()
            ->assertSessionHas('error', 'The coach is unavailable right now. Please try again in a minute.')
            ->assertSessionMissing('coach_answer');

        Http::assertSentCount(2);
        $this->assertSame(0, CoachUsage::count());
    }

    public function test_quota_errors_do_not_try_the_fallback_model()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['error' => ['status' => 'RESOURCE_EXHAUSTED']], 429),
        ]);

        $this->ask()->assertSessionHas('error');

        Http::assertSentCount(1);
    }

    public function test_fallback_can_be_turned_off()
    {
        config(['services.gemini.fallback_model' => null]);
        Http::fake([
            $this->geminiUrl() => Http::response(['error' => ['status' => 'UNAVAILABLE']], 503),
        ]);

        $this->ask()->assertSessionHas('error');

        Http::assertSentCount(1);
    }

    public function test_gemini_blocked_question_shows_a_friendly_message()
    {
        Http::fake([
            $this->geminiUrl() => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']]),
        ]);

        $this->ask()
            ->assertSessionHas('error', "I can't help with that question. Try asking about food, nutrition, or fitness.")
            ->assertSessionMissing('coach_answer');
    }

    public function test_gemini_safety_finish_with_no_text_shows_the_cant_help_message()
    {
        Http::fake([
            $this->geminiUrl() => Http::response([
                'candidates' => [['content' => ['parts' => []], 'finishReason' => 'SAFETY']],
            ]),
        ]);

        $this->ask()->assertSessionHas('error', "I can't help with that question. Try asking about food, nutrition, or fitness.");
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

    public function test_unknown_provider_turns_the_coach_off()
    {
        config(['services.coach.provider' => 'not-a-provider']);
        Http::fake();

        $this->ask()->assertNotFound();

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

    // --- Daily cap -----------------------------------------------------------

    public function test_answered_questions_are_recorded_without_their_text()
    {
        $this->fakeGeminiAnswer('Eat more beans.');
        $user = $this->completedUser();

        $this->actingAs($user)->post(route('coach.ask'), ['question' => 'How do I get more fiber?']);

        $usage = CoachUsage::sole();
        $this->assertSame($user->id, $usage->user_id);
        $this->assertSame('gemini', $usage->provider);
        $this->assertSame(120, $usage->input_tokens);
        $this->assertSame(40, $usage->output_tokens);
    }

    public function test_failed_answers_do_not_use_up_the_daily_cap()
    {
        Http::fake([$this->geminiUrl() => Http::response([], 500)]);

        $this->ask();

        $this->assertSame(0, CoachUsage::count());
    }

    public function test_users_who_hit_the_daily_cap_cannot_ask_more()
    {
        config(['services.coach.daily_limit' => 2]);
        Http::fake();
        $user = $this->completedUser();
        CoachUsage::factory()->count(2)->for($user)->create();

        $this->actingAs($user)
            ->post(route('coach.ask'), ['question' => 'Is rice healthy?'])
            ->assertSessionHas('error', "You've used all 2 coach questions for today. Your questions reset at midnight.");

        Http::assertNothingSent();
    }

    public function test_daily_cap_resets_at_the_users_midnight()
    {
        config(['services.coach.daily_limit' => 1]);
        $this->fakeGeminiAnswer('Answer');
        $user = $this->completedUser(['timezone' => 'America/Chicago']);

        // 11:30 pm Chicago time yesterday is before the user's midnight.
        $this->travelTo(now('America/Chicago')->setTime(12, 0));
        CoachUsage::factory()->for($user)->create([
            'created_at' => now('America/Chicago')->subDay()->setTime(23, 30)->utc(),
        ]);

        $this->actingAs($user)
            ->post(route('coach.ask'), ['question' => 'Is rice healthy?'])
            ->assertSessionHas('coach_answer', 'Answer');
    }

    public function test_coach_page_shows_remaining_questions()
    {
        config(['services.coach.daily_limit' => 5]);
        $user = $this->completedUser();
        CoachUsage::factory()->count(2)->for($user)->create();

        $this->actingAs($user)
            ->get(route('coach'))
            ->assertInertia(fn ($page) => $page->where('remainingToday', 3)->where('dailyLimit', 5));
    }

    // --- Safety rules ----------------------------------------------------------

    public function test_prompt_includes_the_calorie_and_weight_loss_limits()
    {
        $this->assertStringContainsString('fewer than 1,200 calories', CoachService::SYSTEM_PROMPT);
        $this->assertStringContainsString('more than 2 pounds a week', CoachService::SYSTEM_PROMPT);
    }

    public function test_users_under_18_get_the_teen_rules()
    {
        $this->fakeGeminiAnswer('Answer');
        $teen = $this->completedUser(['dob' => now()->subYears(15)->format('Y-m-d')]);

        $this->actingAs($teen)->post(route('coach.ask'), ['question' => 'How do I lose weight?']);

        Http::assertSent(fn (Request $request) => $request['systemInstruction']['parts'][0]['text']
            === CoachService::SYSTEM_PROMPT.CoachService::TEEN_PROMPT);
    }

    public function test_adults_do_not_get_the_teen_rules()
    {
        $this->fakeGeminiAnswer('Answer');
        $adult = $this->completedUser(['dob' => now()->subYears(30)->format('Y-m-d')]);

        $this->actingAs($adult)->post(route('coach.ask'), ['question' => 'How do I lose weight?']);

        Http::assertSent(fn (Request $request) => ! str_contains(
            $request['systemInstruction']['parts'][0]['text'],
            'This user is under 18'
        ));
    }

    // --- Coach turned off ------------------------------------------------------

    public function test_coach_is_hidden_and_unreachable_without_an_api_key()
    {
        config(['services.gemini.key' => null]);
        Http::fake();
        $user = $this->completedUser();

        $this->actingAs($user)->get(route('feedback'))
            ->assertInertia(fn ($page) => $page->where('coachEnabled', false));
        $this->actingAs($user)->get(route('coach'))->assertNotFound();
        $this->actingAs($user)->post(route('coach.ask'), ['question' => 'Is rice healthy?'])->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_coach_link_is_shown_when_an_api_key_is_set()
    {
        $this->actingAs($this->completedUser())->get(route('feedback'))
            ->assertInertia(fn ($page) => $page->where('coachEnabled', true));
    }
}
