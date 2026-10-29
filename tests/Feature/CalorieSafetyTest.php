<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyGoal;
use App\Models\WeightGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalorieSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function goalFor(User $user, int $weeklyGoalId, float $weight = 110, int $activityLevelId = 1): WeightGoal
    {
        return $user->weightGoal()->create([
            'weekly_goal_id' => $weeklyGoalId,
            'activity_level_id' => $activityLevelId,
            'start_weight' => $weight,
            'current_weight' => $weight,
            'goal_weight' => $weight - 10,
            'start_date' => now(),
        ])->fresh();
    }

    private function smallSedentaryAdult(): User
    {
        return User::factory()->completed()->create([
            'gender' => 'female',
            'height_inches' => 60,
            'dob' => now()->subYears(45)->format('Y-m-d'),
        ]);
    }

    public function test_weight_loss_target_never_goes_below_1200_calories()
    {
        $goal = $this->goalFor($this->smallSedentaryAdult(), weeklyGoalId: 4); // lose 2 lbs/week

        // Without the floor this would be maintenance - 1,000.
        $this->assertLessThan(1200, $goal->maintenance_calories + $goal->daily_adjustment);
        $this->assertSame(1200.0, (float) $goal->target_calories);
    }

    public function test_floor_does_not_push_target_above_maintenance()
    {
        // A very small, sedentary user whose maintenance is already under 1,200.
        $user = User::factory()->completed()->create([
            'gender' => 'female',
            'height_inches' => 48,
            'dob' => now()->subYears(80)->format('Y-m-d'),
        ]);
        $goal = $this->goalFor($user, weeklyGoalId: 2, weight: 80);

        $this->assertLessThan(1200, $goal->maintenance_calories);
        $this->assertEquals($goal->maintenance_calories, $goal->target_calories);
    }

    public function test_moderate_deficits_are_unchanged()
    {
        $user = User::factory()->completed()->create([
            'gender' => 'male',
            'height_inches' => 70,
            'dob' => now()->subYears(30)->format('Y-m-d'),
        ]);
        $goal = $this->goalFor($user, weeklyGoalId: 2, weight: 200, activityLevelId: 3); // lose 1 lb/week

        $this->assertEquals($goal->maintenance_calories - 500, $goal->target_calories);
    }

    public function test_users_under_18_get_no_calorie_deficit()
    {
        $teen = User::factory()->completed()->create(['dob' => now()->subYears(15)->format('Y-m-d')]);
        $goal = $this->goalFor($teen, weeklyGoalId: 3, weight: 150);

        $this->assertSame(0, $goal->daily_adjustment);
        $this->assertEquals($goal->maintenance_calories, $goal->target_calories);
    }

    public function test_users_under_18_are_not_offered_weight_loss_goals()
    {
        $teen = User::factory()->completed()->create(['dob' => now()->subYears(15)->format('Y-m-d')]);
        $this->goalFor($teen, weeklyGoalId: 5, weight: 150);

        $this->actingAs($teen)
            ->get(route('weight-goal.edit'))
            ->assertInertia(fn ($page) => $page->where(
                'weeklyGoals',
                fn ($goals) => collect($goals)->pluck('id')->intersect(WeeklyGoal::WEIGHT_LOSS_IDS)->isEmpty()
            ));
    }

    public function test_users_under_18_cannot_save_a_weight_loss_goal()
    {
        $teen = User::factory()->completed()->create(['dob' => now()->subYears(15)->format('Y-m-d')]);
        $this->goalFor($teen, weeklyGoalId: 5, weight: 150);

        $this->actingAs($teen)->put(route('weight-goal.update'), [
            'weekly_goal_id' => 2,
            'activity_level_id' => 3,
            'current_weight' => 150,
            'goal_weight' => 140,
        ])->assertSessionHasErrors('weekly_goal_id');
    }

    public function test_adults_are_offered_every_goal()
    {
        $adult = $this->smallSedentaryAdult();
        $this->goalFor($adult, weeklyGoalId: 5);

        $this->actingAs($adult)
            ->get(route('weight-goal.edit'))
            ->assertInertia(fn ($page) => $page->has('weeklyGoals', WeeklyGoal::count()));
    }

    public function test_unknown_goal_and_activity_ids_are_rejected()
    {
        $adult = $this->smallSedentaryAdult();
        $this->goalFor($adult, weeklyGoalId: 5);

        $this->actingAs($adult)->put(route('weight-goal.update'), [
            'weekly_goal_id' => 99,
            'activity_level_id' => 99,
            'current_weight' => 150,
            'goal_weight' => 140,
        ])->assertSessionHasErrors(['weekly_goal_id', 'activity_level_id']);
    }

    public function test_goal_7_is_labeled_as_gaining_weight()
    {
        $this->assertSame('Gain 1 lb per week', DB::table('weekly_goals')->where('id', 7)->value('name'));
        $this->assertSame(500, WeightGoal::make(['weekly_goal_id' => 7])->daily_adjustment);
    }
}
