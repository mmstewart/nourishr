<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    public function test_admins_can_impersonate_regular_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('impersonate', $user))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_regular_users_cannot_impersonate(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post(route('impersonate', $other))->assertForbidden();
        $this->assertAuthenticatedAs($user);
    }

    public function test_admins_cannot_impersonate_other_admins(): void
    {
        $this->actingAs($this->admin())->post(route('impersonate', $this->admin()))->assertForbidden();
    }

    public function test_impersonation_requires_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())->get('/impersonate/'.$user->id)->assertMethodNotAllowed();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('impersonate', $user))->assertRedirect(route('login'));
    }
}