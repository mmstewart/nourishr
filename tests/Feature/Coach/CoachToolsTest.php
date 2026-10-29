<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use App\Services\Coach\CoachTools;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->travelTo(now('America/Chicago')->setDate(2026, 10, 29)->setTime(12, 0));
    }

    private function user(): User
    {
        return User::factory()->completed()->create(['timezone' => 'America/Chicago']);
    }

    private function logMeal(User $user, string $date, string $food, int $calories, float $protein = 10): void
    {
        $user->nutritionEntries()->create([
            'date' => $date,
            'meal_type' => 'lunch',
            'food_item' => $food,
            'calories' => $calories,
            'protein' => $protein,
            'carbohydrates' => 20,
            'fats' => 5,
            'serving_size' => '100g',
            'quantity' => 1,
        ]);
    }

    public function test_nutrition_log_returns_daily_totals_and_meals()
    {
        $user = $this->user();
        $this->logMeal($user, '2026-10-28', 'Oatmeal', 300, 10);
        $this->logMeal($user, '2026-10-28', 'Chicken', 400, 40);
        $this->logMeal($user, '2026-10-29', 'Salad', 200, 5);

        $result = (new CoachTools($user))->run('get_nutrition_log', ['start_date' => '2026-10-28', 'end_date' => '2026-10-29']);

        $this->assertSame(2, $result['days_logged']);
        $this->assertSame('2026-10-28', $result['days'][0]['date']);
        $this->assertSame(700, $result['days'][0]['totals']['calories']);
        $this->assertSame(50.0, $result['days'][0]['totals']['protein_g']);
        $this->assertCount(2, $result['days'][0]['meals']);
        $this->assertSame('Salad', $result['days'][1]['meals'][0]['food']);
    }

    public function test_tools_only_return_the_users_own_data()
    {
        $user = $this->user();
        $someoneElse = $this->user();
        $this->logMeal($user, '2026-10-29', 'Mine', 300);
        $this->logMeal($someoneElse, '2026-10-29', 'Not mine', 900);
        $someoneElse->weightEntries()->create(['date' => '2026-10-29', 'weight' => 250]);

        $tools = new CoachTools($user);
        $nutrition = $tools->run('get_nutrition_log', []);
        $weights = $tools->run('get_weight_history', []);

        $this->assertSame(['Mine'], collect($nutrition['days'])->flatMap(fn ($day) => collect($day['meals'])->pluck('food'))->all());
        $this->assertSame([], $weights['entries']);
    }

    public function test_date_range_defaults_to_the_last_7_days()
    {
        $result = (new CoachTools($this->user()))->run('get_nutrition_log', []);

        $this->assertSame('2026-10-23', $result['start_date']);
        $this->assertSame('2026-10-29', $result['end_date']);
    }

    public function test_date_range_is_capped_at_31_days()
    {
        $result = (new CoachTools($this->user()))->run('get_weight_history', [
            'start_date' => '2025-01-01',
            'end_date' => '2026-10-29',
        ]);

        $this->assertSame('2026-09-29', $result['start_date']);
        $this->assertSame('2026-10-29', $result['end_date']);
        $this->assertArrayHasKey('note', $result);
    }

    public function test_future_and_reversed_dates_are_corrected()
    {
        $tools = new CoachTools($this->user());

        $future = $tools->run('get_nutrition_log', ['start_date' => '2026-10-27', 'end_date' => '2027-01-01']);
        $this->assertSame('2026-10-29', $future['end_date']);

        $reversed = $tools->run('get_nutrition_log', ['start_date' => '2026-10-29', 'end_date' => '2026-10-20']);
        $this->assertSame('2026-10-20', $reversed['start_date']);
        $this->assertSame('2026-10-29', $reversed['end_date']);
    }

    public function test_invalid_dates_fall_back_to_defaults()
    {
        $result = (new CoachTools($this->user()))->run('get_nutrition_log', [
            'start_date' => 'last week',
            'end_date' => '2026-13-45',
        ]);

        $this->assertSame('2026-10-23', $result['start_date']);
        $this->assertSame('2026-10-29', $result['end_date']);
    }

    public function test_weight_history_returns_entries_in_order()
    {
        $user = $this->user();
        $user->weightEntries()->create(['date' => '2026-10-28', 'weight' => 181.5]);
        $user->weightEntries()->create(['date' => '2026-10-25', 'weight' => 183]);

        $result = (new CoachTools($user))->run('get_weight_history', []);

        $this->assertSame([
            ['date' => '2026-10-25', 'weight_lbs' => 183.0],
            ['date' => '2026-10-28', 'weight_lbs' => 181.5],
        ], $result['entries']);
    }

    public function test_goals_return_targets()
    {
        $user = $this->user();
        $user->weightGoal()->create([
            'weekly_goal_id' => 2,
            'activity_level_id' => 3,
            'start_weight' => 200,
            'current_weight' => 190,
            'goal_weight' => 170,
            'start_date' => now(),
        ]);

        $result = (new CoachTools($user))->run('get_goals', []);

        $this->assertSame(170.0, $result['goal_weight_lbs']);
        $this->assertSame('Lose 1 lb per week', $result['weekly_goal']);
        $this->assertSame(
            (int) $user->weightGoal->fresh()->target_calories,
            $result['daily_targets']['calories']
        );
    }

    public function test_goals_explain_when_none_is_set()
    {
        $user = User::factory()->create(['timezone' => 'America/Chicago']);

        $this->assertArrayHasKey('error', (new CoachTools($user))->run('get_goals', []));
    }

    public function test_food_search_returns_top_results_per_100g()
    {
        Http::fake([
            'api.nal.usda.gov/*' => Http::response(['foods' => array_fill(0, 8, [
                'description' => 'Greek yogurt, plain',
                'foodNutrients' => [
                    ['nutrientName' => 'Energy', 'value' => 59],
                    ['nutrientName' => 'Protein', 'value' => 10.3],
                    ['nutrientName' => 'Carbohydrate, by difference', 'value' => 3.6],
                    ['nutrientName' => 'Total lipid (fat)', 'value' => 0.4],
                ],
            ])]),
        ]);

        $result = (new CoachTools($this->user()))->run('search_food', ['query' => 'greek yogurt']);

        $this->assertCount(CoachTools::MAX_FOOD_RESULTS, $result['foods']);
        $this->assertSame(10.3, $result['foods'][0]['protein_g']);
        Http::assertSent(fn ($request) => $request['query'] === 'greek yogurt' && $request['pageSize'] === CoachTools::MAX_FOOD_RESULTS);
    }

    public function test_food_search_requires_a_query()
    {
        Http::fake();

        $this->assertArrayHasKey('error', (new CoachTools($this->user()))->run('search_food', ['query' => '  ']));
        Http::assertNothingSent();
    }

    public function test_unknown_tools_return_an_error_instead_of_failing()
    {
        $this->assertArrayHasKey('error', (new CoachTools($this->user()))->run('delete_everything', []));
    }
}
