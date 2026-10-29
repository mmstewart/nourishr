<?php

namespace Tests\Feature\Coach;

use App\Exceptions\CoachUnavailableException;
use App\Models\CoachUsage;
use App\Models\User;
use App\Services\Coach\ClaudeProvider;
use App\Services\Coach\CoachDeadline;
use App\Services\Coach\GeminiProvider;
use App\Services\CoachService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachToolLoopTest extends TestCase
{
    use RefreshDatabase;

    private const MODEL = 'gemini-test-model';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        config([
            'services.coach.provider' => 'gemini',
            'services.coach.max_tool_rounds' => 4,
            'services.gemini.key' => 'gemini-test-key',
            'services.gemini.model' => self::MODEL,
            'services.gemini.fallback_model' => null,
            'services.anthropic.key' => 'claude-test-key',
            'services.anthropic.model' => 'claude-test-model',
        ]);
    }

    private function user(bool $dataAccess = true): User
    {
        $user = User::factory()->completed()->create([
            'timezone' => 'America/Chicago',
            'coach_data_access' => $dataAccess,
        ]);

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

    private function geminiCall(string $name, array $args = [], string $id = 'call-1'): array
    {
        return [
            'candidates' => [[
                'content' => ['role' => 'model', 'parts' => [[
                    'functionCall' => ['name' => $name, 'args' => $args, 'id' => $id],
                    'thoughtSignature' => 'signature-'.$id,
                ]]],
                'finishReason' => 'STOP',
            ]],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 10],
        ];
    }

    private function geminiText(string $text): array
    {
        return [
            'candidates' => [['content' => ['role' => 'model', 'parts' => [['text' => $text]]], 'finishReason' => 'STOP']],
            'usageMetadata' => ['promptTokenCount' => 300, 'candidatesTokenCount' => 50],
            'modelVersion' => self::MODEL,
        ];
    }

    private function ask(User $user, string $question = 'How was my protein this week?')
    {
        return $this->actingAs($user)->post(route('coach.ask'), ['question' => $question]);
    }

    public function test_gemini_calls_a_tool_and_answers_from_the_result()
    {
        $user = $this->user();
        $user->nutritionEntries()->create([
            'date' => now('America/Chicago')->toDateString(),
            'meal_type' => 'lunch', 'food_item' => 'Chicken', 'calories' => 400,
            'protein' => 40, 'carbohydrates' => 0, 'fats' => 10, 'serving_size' => '100g', 'quantity' => 1,
        ]);

        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::sequence()
            ->push($this->geminiCall('get_nutrition_log', ['start_date' => now('America/Chicago')->toDateString()]))
            ->push($this->geminiText('You had 40 g of protein today.')),
        ]);

        $this->ask($user)->assertSessionHas('coach_answer', 'You had 40 g of protein today.');

        Http::assertSentCount(2);
        $requests = Http::recorded()->map(fn ($pair) => $pair[0]);
        // Decode the JSON actually sent, as Gemini would receive it.
        $followUp = json_decode($requests[1]->body(), true)['contents'];

        // The model's turn goes back unchanged, thought signature included.
        $this->assertSame('signature-call-1', $followUp[1]['parts'][0]['thoughtSignature']);
        $this->assertSame('model', $followUp[1]['role']);

        // The tool result goes back with the call's id and the user's real data.
        $result = $followUp[2]['parts'][0]['functionResponse'];
        $this->assertSame('call-1', $result['id']);
        $this->assertSame('get_nutrition_log', $result['name']);
        $this->assertSame(40, (int) $result['response']['days'][0]['totals']['protein_g']);

        // Usage covers both calls.
        $usage = CoachUsage::sole();
        $this->assertSame(400, $usage->input_tokens);
        $this->assertSame(60, $usage->output_tokens);
    }

    public function test_tools_are_declared_only_when_the_user_opted_in()
    {
        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::response($this->geminiText('General answer'))]);

        $this->ask($this->user(dataAccess: false), 'Is oatmeal healthy?');

        Http::assertSent(fn (Request $request) => ! isset($request['tools'])
            && ! str_contains($request['systemInstruction']['parts'][0]['text'], 'You can call tools'));
    }

    public function test_opted_in_requests_declare_all_four_tools_and_todays_date()
    {
        $this->travelTo(now('America/Chicago')->setDate(2026, 10, 29)->setTime(12, 0));
        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::response($this->geminiText('Answer'))]);

        $this->ask($this->user());

        Http::assertSent(function (Request $request) {
            $names = collect($request['tools'][0]['functionDeclarations'])->pluck('name')->all();

            return $names === ['get_nutrition_log', 'get_weight_history', 'get_goals', 'search_food']
                && $request['toolConfig']['functionCallingConfig']['mode'] === 'AUTO'
                && str_contains($request['systemInstruction']['parts'][0]['text'], 'Today is Thursday, October 29, 2026');
        });
    }

    public function test_tool_loop_stops_after_the_round_limit()
    {
        config(['services.coach.max_tool_rounds' => 2]);
        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::sequence()
            ->push($this->geminiCall('get_goals', [], 'a'))
            ->push($this->geminiCall('get_goals', [], 'b'))
            ->push($this->geminiText('Here is what I found.')),
        ]);

        $this->ask($this->user())->assertSessionHas('coach_answer', 'Here is what I found.');

        Http::assertSentCount(3);
        $lastRequest = Http::recorded()->last()[0];
        $this->assertSame('NONE', $lastRequest['toolConfig']['functionCallingConfig']['mode']);
    }

    public function test_daily_cap_counts_one_question_however_many_tool_calls()
    {
        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::sequence()
            ->push($this->geminiCall('get_goals', [], 'a'))
            ->push($this->geminiCall('get_weight_history', [], 'b'))
            ->push($this->geminiText('Answer')),
        ]);
        $user = $this->user();

        $this->ask($user);

        $this->assertSame(1, CoachUsage::count());
        $this->assertSame(config('services.coach.daily_limit') - 1, app(CoachService::class)->remainingToday($user));
    }

    public function test_claude_calls_a_tool_and_answers_from_the_result()
    {
        config(['services.coach.provider' => 'anthropic']);
        Http::fake([ClaudeProvider::API_URL => Http::sequence()
            ->push([
                'content' => [
                    ['type' => 'text', 'text' => 'Let me check your goals.'],
                    ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'get_goals', 'input' => new \stdClass],
                ],
                'stop_reason' => 'tool_use',
                'usage' => ['input_tokens' => 100, 'output_tokens' => 20],
            ])
            ->push([
                'content' => [['type' => 'text', 'text' => 'Your goal is 160 lbs.']],
                'stop_reason' => 'end_turn',
                'model' => 'claude-test-model',
                'usage' => ['input_tokens' => 200, 'output_tokens' => 30],
            ]),
        ]);

        $this->ask($this->user(), 'What is my goal weight?')->assertSessionHas('coach_answer', 'Your goal is 160 lbs.');

        $followUp = Http::recorded()->map(fn ($pair) => $pair[0])[1]['messages'];
        $this->assertSame('assistant', $followUp[1]['role']);
        $this->assertSame('tool_result', $followUp[2]['content'][0]['type']);
        $this->assertSame('toolu_1', $followUp[2]['content'][0]['tool_use_id']);
        $this->assertEquals(160, json_decode($followUp[2]['content'][0]['content'], true)['goal_weight_lbs']);
    }

    public function test_gemini_tool_call_with_no_arguments_is_sent_back_as_an_object()
    {
        // Raw JSON, as Gemini sends it: an empty object, not an empty list.
        $toolCall = '{"candidates":[{"content":{"role":"model","parts":[{"functionCall":{"name":"get_goals","args":{},"id":"call-1"},"thoughtSignature":"sig"}]},"finishReason":"STOP"}]}';

        Http::fake([GeminiProvider::urlFor(self::MODEL) => Http::sequence()
            ->push($toolCall)
            ->push($this->geminiText('Your goal is 160 lbs.')),
        ]);

        $this->ask($this->user(), 'What is my goal?')->assertSessionHas('coach_answer', 'Your goal is 160 lbs.');

        $followUp = Http::recorded()->map(fn ($pair) => $pair[0])[1]->body();
        $this->assertStringContainsString('"args":{}', $followUp);
        $this->assertStringNotContainsString('"args":[]', $followUp);
    }

    public function test_claude_tool_call_with_no_input_is_sent_back_as_an_object()
    {
        config(['services.coach.provider' => 'anthropic']);
        $toolUse = '{"content":[{"type":"tool_use","id":"toolu_1","name":"get_goals","input":{}}],"stop_reason":"tool_use","usage":{"input_tokens":10,"output_tokens":5}}';

        Http::fake([ClaudeProvider::API_URL => Http::sequence()
            ->push($toolUse)
            ->push(['content' => [['type' => 'text', 'text' => 'Done.']], 'stop_reason' => 'end_turn', 'usage' => ['input_tokens' => 1, 'output_tokens' => 1]]),
        ]);

        $this->ask($this->user(), 'What is my goal?')->assertSessionHas('coach_answer', 'Done.');

        $followUp = Http::recorded()->map(fn ($pair) => $pair[0])[1]->body();
        $this->assertStringContainsString('"input":{}', $followUp);
        $this->assertStringNotContainsString('"input":[]', $followUp);
    }

    public function test_users_can_turn_data_access_on_and_off()
    {
        $user = $this->user(dataAccess: false);

        $this->actingAs($user)->get(route('coach'))
            ->assertInertia(fn ($page) => $page->where('dataAccess', false));

        $this->actingAs($user)->put(route('coach.data-access'), ['enabled' => true])->assertRedirect(route('coach'));
        $this->assertTrue($user->fresh()->coach_data_access);

        $this->actingAs($user)->put(route('coach.data-access'), ['enabled' => false]);
        $this->assertFalse($user->fresh()->coach_data_access);
    }

    public function test_data_access_is_off_by_default()
    {
        $this->assertFalse(User::factory()->create()->fresh()->coach_data_access);
    }

    public function test_deadline_stops_further_calls_when_time_runs_out()
    {
        $this->expectException(CoachUnavailableException::class);

        (new CoachDeadline(totalSeconds: 1))->timeoutForNextCall();
    }
}
