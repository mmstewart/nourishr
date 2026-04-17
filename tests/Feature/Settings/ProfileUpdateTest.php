<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->completed()->create();

        $data = User::factory()->completed()->make([
            'name' => 'Test User',
            'dob' => '1995-05-15',
            'feet' => 6,
            'inches' => 2,
            'timezone' => 'America/Chicago',
        ])->toArray();

        $response = $this->actingAs($user)
            ->put(route('profile.update'), $data);

        $response->assertSessionHasNoErrors()->assertRedirect();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals(70, $user->height_inches);
        $this->assertEquals('America/Chicago', $user->timezone);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->verified()->completed()->create();

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                ...$user->toArray(),
                'feet' => floor($user->height_inches / 12),
                'inches' => $user->height_inches % 12,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
        $this->assertNotNull($user->refresh()->email_verified_at);
    }
}
