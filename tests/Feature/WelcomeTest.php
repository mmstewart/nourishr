<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_welcome_page_shows_login_register_buttons_when_guest()
    {
        $response = $this->get('/');

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Welcome')
                ->where('isLoggedIn', false)
                ->where('canRegister', true)
        );
    }

    public function test_welcome_page_shows_continue_setup_button_when_logged_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Welcome')
                ->where('isLoggedIn', true)
        );
    }

    public function test_guest_users_see_login_and_register_options()
    {
        $response = $this->get('/');

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Welcome')
                ->where('isLoggedIn', false)
        );
    }

    public function test_logged_in_users_can_still_access_the_welcome_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Welcome')
                ->where('isLoggedIn', true)
        );
    }
}
