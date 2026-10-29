<?php

namespace Tests\Feature\Entries;

use App\Models\NutritionEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Entries remember where their food came from (USDA fdc_id or scanned
 * barcode) so the edit page can load the right serving sizes.
 */
class NutritionEntrySourceTest extends TestCase
{
    use RefreshDatabase;

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

    private function entryData(array $overrides = []): array
    {
        return [
            'meal_type' => 'breakfast',
            'food_item' => 'Oatmeal',
            'calories' => 350,
            'protein' => 12,
            'carbohydrates' => 60,
            'fats' => 6,
            'serving_size' => '1 cup (100g)',
            'quantity' => 1,
            ...$overrides,
        ];
    }

    private function createEntry(User $user, array $overrides = []): NutritionEntry
    {
        return $user->nutritionEntries()->create([
            'date' => now()->format('Y-m-d'),
            ...$this->entryData($overrides),
        ]);
    }

    public function test_entries_save_their_usda_id_and_barcode()
    {
        $user = $this->completedUser();

        $this->actingAs($user)->post(route('nutrition-entries.store'), [
            'date' => now()->format('Y-m-d'),
            'entries' => [
                $this->entryData(['fdc_id' => 173904, 'barcode' => null]),
                $this->entryData(['food_item' => 'Granola bar', 'fdc_id' => null, 'barcode' => '0123456789012']),
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('nutrition-entries.index'));

        $this->assertDatabaseHas('nutrition_entries', ['user_id' => $user->id, 'fdc_id' => 173904, 'barcode' => null]);
        $this->assertDatabaseHas('nutrition_entries', ['user_id' => $user->id, 'fdc_id' => null, 'barcode' => '0123456789012']);
    }

    public function test_barcodes_must_be_digits()
    {
        $this->actingAs($this->completedUser())->post(route('nutrition-entries.store'), [
            'date' => now()->format('Y-m-d'),
            'entries' => [$this->entryData(['barcode' => 'not-a-barcode'])],
        ])->assertSessionHasErrors('entries.0.barcode');
    }

    public function test_editing_an_entry_with_no_source_uses_the_default_serving()
    {
        Http::fake();
        $user = $this->completedUser();
        $entry = $this->createEntry($user);

        $this->actingAs($user)
            ->get(route('nutrition-entries.edit', $entry))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('servingOptions', [['label' => '100g', 'gramWeight' => 100]]));

        Http::assertNothingSent();
    }

    public function test_editing_a_scanned_entry_loads_open_food_facts_servings()
    {
        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'product' => ['serving_size' => '1 bar (40g)', 'serving_quantity' => 40],
            ]),
        ]);
        $user = $this->completedUser();
        $entry = $this->createEntry($user, ['barcode' => '0123456789012']);

        $this->actingAs($user)
            ->get(route('nutrition-entries.edit', $entry))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('servingOptions', [
                ['label' => '100g', 'gramWeight' => 100],
                ['label' => '1 bar (40g)', 'gramWeight' => 40],
            ]));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/product/0123456789012.json'));
    }

    public function test_editing_a_usda_entry_loads_usda_servings()
    {
        Http::fake([
            'api.nal.usda.gov/*' => Http::response([
                'foodPortions' => [['portionDescription' => '1 Cup', 'gramWeight' => 81]],
            ]),
        ]);
        $user = $this->completedUser();
        $entry = $this->createEntry($user, ['fdc_id' => 173904]);

        $this->actingAs($user)
            ->get(route('nutrition-entries.edit', $entry))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('servingOptions', [
                ['label' => '100g', 'gramWeight' => 100],
                ['label' => '1 cup', 'gramWeight' => 81],
            ]));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/fdc/v1/food/173904'));
    }
}
