<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_screen_can_be_rendered()
    {
        $response = $this->get(route('setup'));

        $response->assertOk();
    }

    public function test_user_can_create_account()
    {
        $response = $this->post(route('setup.account'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('setup'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_cannot_create_account_with_invalid_name()
    {
        $response = $this->post(route('setup.account'), [
            'name' => 'Bitch', // Invalid name
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_complete_profile()
    {
        $user = User::factory()->create();

        $dob = now()->subYears(25)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('setup.profile'), [
            'dob' => $dob,
            'feet' => 5,
            'inches' => 10,
            'height_inches' => 70,
            'gender' => 'male',
            'country_code' => 'US',
            'timezone' => 'America/New_York',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('setup'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'dob' => $dob,
            'height_inches' => 70,
            'timezone' => 'America/New_York',
        ]);
    }

    public function test_user_cannot_complete_profile_under_13()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('setup.profile'), [
            'dob' => now()->subYears(10)->format('Y-m-d'),
            'feet' => 5,
            'inches' => 1,
            'height_inches' => 61,
            'gender' => 'male',
            'country_code' => 'US',
            'timezone' => 'America/New_York',
        ]);

        $response->assertSessionHasErrors([
            'dob' => 'You must be at least 13 years old to use Nourishr.'
        ]);
    }

    public function test_user_can_setup_weight_goal()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('setup.weight-goal'), [
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'current_weight' => 180,
            'goal_weight' => 160,
        ]);

        $response->assertRedirect(route('setup'));

        $this->assertDatabaseHas('weight_goals', [
            'user_id' => $user->id,
            'start_weight' => 180,
            'goal_weight' => 160,
        ]);

        $this->assertDatabaseHas('weight_entries', [
            'user_id' => $user->id,
            'weight' => 180,
        ]);
    }
}
