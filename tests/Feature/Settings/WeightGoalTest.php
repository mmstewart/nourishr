<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_goal_settings_screen_can_be_rendered()
    {
        $user = User::factory()->completed()->create();

        $response = $this->actingAs($user)->get(route('weight-goal.edit'));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('settings/WeightGoal')
                    ->has('weeklyGoals')
                    ->has('activityLevels')
            );
    }

    public function test_weight_goals_can_be_updated()
    {
        $this->seed();

        $user = User::factory()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 1,
            'start_weight' => 200,
            'current_weight' => 200,
            'goal_weight' => 180,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('weight-goal.update'), [
            'weekly_goal_id' => 2,
            'activity_level_id' => 4,
            'current_weight' => 175.5,
            'goal_weight' => 165,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('weight_goals', [
            'user_id' => $user->id,
            'current_weight' => 175.5,
            'goal_weight' => 165,
            'weekly_goal_id' => 2,
        ]);
    }

    public function test_weight_goals_require_valid_data()
    {
        $user = User::factory()->completed()->create();

        $response = $this->actingAs($user)->put(route('weight-goal.update'), [
            'current_weight' => 'not-a-number',
            'goal_weight' => 1000,
        ]);

        $response->assertSessionHasErrors(['current_weight', 'goal_weight']);
    }
}
