<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->seed();

        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_has_links_to_add_weight_and_nutrition()
    {
        $this->seed();

        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->where('auth.user.name', $user->name)
        );
    }

    public function test_user_can_access_weight_entry_page_from_dashboard()
    {
        $this->seed();

        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->get(route('weight-entries.create'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('weight-entries/Create')
        );
    }

    public function test_user_can_access_nutrition_entry_page_from_dashboard()
    {
        $this->seed();

        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->get(route('nutrition-entries.create'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('nutrition-entries/Create')
        );
    }
}
