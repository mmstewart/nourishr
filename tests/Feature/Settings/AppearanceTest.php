<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_settings_page_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('appearance.edit'));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('settings/Appearance')
            );
    }

    public function test_guests_cannot_access_appearance_settings()
    {
        $response = $this->get(route('appearance.edit'));

        $response->assertRedirect(route('login'));
    }
}
