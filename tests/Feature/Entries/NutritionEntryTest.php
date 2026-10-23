<?php

namespace Tests\Feature\Entries;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NutritionEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_nutrition_entries_index_page_can_be_rendered()
    {
        $user = User::factory()->completed()->create();

        $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Oatmeal',
            'serving_size' => '1 cup (100g)',
            'quantity' => 1.5,
            'meal_type' => 'breakfast',
            'calories' => 350,
            'protein' => 12,
            'carbohydrates' => 60,
            'fats' => 6,
        ]);

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

        $response = $this->actingAs($user)->get(route('nutrition-entries.index'));

        $response->assertStatus(200);
    }

    public function test_users_cannot_view_others_nutrition_entries()
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

        $user1->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $user2->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $user2->weightEntries()->create([
            'date' => now(),
            'weight' => 180,
        ]);

        $user2->nutritionEntries()->create([
            'date' => '2026-01-01',
            'food_item' => 'Egg Whites',
            'serving_size' => 'Whole Egg',
            'quantity' => 2,
            'meal_type' => 'breakfast',
            'calories' => 140,
            'protein' => 12,
            'carbohydrates' => 1,
            'fats' => 10,
        ]);

        $response = $this->actingAs($user1)->get(route('nutrition-entries.index'));

        $response->assertOk();
        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('nutritionEntries', 0)
        );
    }

    public function test_nutrition_entry_fields_are_validated_on_store()
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

        $response = $this->actingAs($user)->post(route('nutrition-entries.store'), [
            'date' => now()->format('Y-m-d'),
            'entries' => [
                [
                    'food_item' => '',
                    'quantity' => 0.10,
                    'meal_type' => 'invalid-meal',
                    'calories' => 100,
                    'protein' => 10.00,
                    'carbohydrates' => 15.00,
                    'fats' => 2.50,
                    'serving_size' => '100g',
                ]
            ]
        ]);

        $response->assertSessionHasErrors([
            'entries.0.food_item',
            'entries.0.quantity',
            'entries.0.meal_type'
        ]);
    }

    public function test_nutrition_date_cannot_be_in_the_future()
    {
        $user = User::factory()->completed()->create();

        $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Oatmeal',
            'serving_size' => '1 cup (100g)',
            'quantity' => 1.5,
            'meal_type' => 'breakfast',
            'calories' => 350,
            'protein' => 12,
            'carbohydrates' => 60,
            'fats' => 6,
        ]);

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

        $response = $this->actingAs($user)->post(route('nutrition-entries.store'), [
            'date' => now()->addDay()->format('Y-m-d'),
            'food_item' => 'Apple',
            'serving_size' => 'Medium',
            'quantity' => 1,
            'meal_type' => 'snack',
            'calories' => 95,
            'protein' => 0.5,
            'carbohydrates' => 25,
            'fats' => 0.3,
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_a_nutrition_entry_can_be_updated()
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

        $entry = $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Chicken Breast',
            'serving_size' => '100g',
            'quantity' => 1,
            'meal_type' => 'lunch',
            'calories' => 165,
            'protein' => 31,
            'carbohydrates' => 0,
            'fats' => 3.6,
        ]);

        $response = $this->actingAs($user)->patch(route('nutrition-entries.update', $entry), [
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Chicken Breast',
            'serving_size' => '100g',
            'quantity' => 2,
            'meal_type' => 'lunch',
            'calories' => 330,
            'protein' => 62,
            'carbohydrates' => 0,
            'fats' => 7.2,
        ]);

        $response->assertRedirect(route('nutrition-entries.show', $entry));

        $this->assertDatabaseHas('nutrition_entries', [
            'id' => $entry->id,
            'quantity' => 2,
            'protein' => 62
        ]);
    }

    public function test_a_nutrition_entry_can_be_deleted()
    {
        $user = User::factory()->completed()->create();

        $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Oatmeal',
            'serving_size' => '1 cup (100g)',
            'quantity' => 1.5,
            'meal_type' => 'breakfast',
            'calories' => 350,
            'protein' => 12,
            'carbohydrates' => 60,
            'fats' => 6,
        ]);

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

        $entry = $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            'food_item' => 'Protein Shake',
            'serving_size' => '1 scoop',
            'quantity' => 1,
            'meal_type' => 'snack',
            'calories' => 120,
            'protein' => 24,
            'carbohydrates' => 3,
            'fats' => 1.5,
        ]);

        $response = $this->actingAs($user)->delete(route('nutrition-entries.destroy', $entry));

        $response->assertRedirect(route('nutrition-entries.index'));

        $this->assertDatabaseMissing('nutrition_entries', [
            'id' => $entry->id
        ]);
    }
}
