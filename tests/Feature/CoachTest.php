<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CoachService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.anthropic.key' => 'test-key',
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

    private function fakeClaudeAnswer(string $text): void
    {
        Http::fake([
            CoachService::API_URL => Http::response([
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

    public function test_coach_screen_can_be_rendered()
    {
        $this->actingAs($this->completedUser())
            ->get(route('coach'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Coach'));
    }

    public function test_guests_cannot_use_the_coach()
    {
        $this->get(route('coach'))->assertRedirect(route('login'));
        $this->post(route('coach.ask'), ['question' => 'Is rice healthy?'])->assertRedirect(route('login'));

        Http::assertNothingSent();
    }

    public function test_user_gets_an_answer_from_the_coach()
    {
        $this->fakeClaudeAnswer('Try Greek yogurt with berries.');

        $response = $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => 'What is a high-protein breakfast?']);

        $response->assertRedirect(route('coach'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('coach_answer', 'Try Greek yogurt with berries.');
        $response->assertSessionHas('coach_question', 'What is a high-protein breakfast?');
    }

    public function test_answer_is_shown_on_the_coach_page()
    {
        $this->fakeClaudeAnswer('Try Greek yogurt with berries.');

        $this->actingAs($this->completedUser())
            ->followingRedirects()
            ->post(route('coach.ask'), ['question' => 'What is a high-protein breakfast?'])
            ->assertInertia(fn ($page) => $page
                ->component('Coach')
                ->where('question', 'What is a high-protein breakfast?')
                ->where('answer', 'Try Greek yogurt with berries.'));
    }

    public function test_request_sends_the_question_model_and_api_headers()
    {
        $this->fakeClaudeAnswer('Answer');

        $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => 'How much water should I drink?']);

        Http::assertSent(function (Request $request) {
            return $request->url() === CoachService::API_URL
                && $request->hasHeader('x-api-key', 'test-key')
                && $request->hasHeader('anthropic-version', CoachService::API_VERSION)
                && $request['model'] === 'claude-test-model'
                && $request['system'] === CoachService::SYSTEM_PROMPT
                && $request['messages'] === [['role' => 'user', 'content' => 'How much water should I drink?']];
        });
    }

    public function test_api_error_shows_a_friendly_message()
    {
        Http::fake([
            CoachService::API_URL => Http::response(['type' => 'error', 'error' => ['type' => 'overloaded_error']], 529),
        ]);

        $response = $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => 'Is oatmeal good for me?']);

        $response->assertRedirect(route('coach'));
        $response->assertSessionHas('error');
        $response->assertSessionMissing('coach_answer');
    }

    public function test_empty_answer_is_treated_as_unavailable()
    {
        Http::fake([
            CoachService::API_URL => Http::response(['content' => [], 'stop_reason' => 'end_turn']),
        ]);

        $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => 'Is oatmeal good for me?'])
            ->assertSessionHas('error')
            ->assertSessionMissing('coach_answer');
    }

    public function test_missing_api_key_does_not_call_the_api()
    {
        config(['services.anthropic.key' => null]);
        Http::fake();

        $this->actingAs($this->completedUser())
            ->post(route('coach.ask'), ['question' => 'Is oatmeal good for me?'])
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

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
        $this->fakeClaudeAnswer('Answer');
        $user = $this->completedUser();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('coach.ask'), ['question' => 'Is rice healthy?']);
        }

        $this->actingAs($user)
            ->post(route('coach.ask'), ['question' => 'Is rice healthy?'])
            ->assertStatus(429);
    }
}
