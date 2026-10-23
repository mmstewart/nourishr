<?php

namespace Tests\Feature\Entries;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WeightEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_entries_index_page_can_be_rendered()
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

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->get(route('weight-entries.index'));

        $response->assertStatus(200);
    }

    public function test_users_cannot_view_others_weight_entries()
    {
        $user1 = User::factory()->completed()->create();
        $user2 = User::factory()->completed()->create();

        $user1->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user2->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 250,
            'current_weight' => 250,
            'goal_weight' => 200,
            'start_date' => now(),
        ]);

        $user2->weightEntries()->create([
            'date' => '2026-01-01',
            'weight' => 250
        ]);

        $response = $this->actingAs($user1)->get(route('weight-entries.index'));

        $response->assertOk();

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('weightEntries', 0)
        );
    }

    public function test_weight_must_be_within_realistic_limits()
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

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->post(route('weight-entries.store'), [
            'weight' => 10,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('weight');
    }

    public function test_weight_date_cannot_be_in_the_future()
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

        $user->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $response = $this->actingAs($user)->post(route('weight-entries.store'), [
            'weight' => 180,
            'date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_index_filters_entries_by_time_range()
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

        $user->weightEntries()->create(['date' => now()->subMonths(2), 'weight' => 200]);
        $user->weightEntries()->create(['date' => now()->subDay(), 'weight' => 195]);

        // Just visit the page without the ['range' => '7'] parameter
        $response = $this->actingAs($user)->get(route('weight-entries.index'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('weightEntries', 2)
                ->where('weightEntries.0.weight', 195)
        );
    }

    public function test_a_weight_entry_can_be_updated()
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

        $entry = $user->weightEntries()->create(['date' => now(), 'weight' => 180]);

        $response = $this->actingAs($user)->patch(route('weight-entries.update', $entry), [
            'weight' => 175,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('weight-entries.index'));

        $this->assertDatabaseHas('weight_entries', [
            'id' => $entry->id,
            'weight' => 175
        ]);
    }
}
