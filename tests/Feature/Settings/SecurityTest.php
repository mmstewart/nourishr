<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_settings_page_can_be_rendered()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withoutTwoFactor()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.show'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('settings/Security')
                    ->where('twoFactorEnabled', false)
            );
    }

    public function test_security_settings_page_requires_password_confirmation_when_enabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('security.show'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_security_settings_page_does_not_requires_password_confirmation_when_disabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);

        $this->actingAs($user)
            ->get(route('security.show'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('settings/Security')
            );
    }

    public function test_security_settings_page_returns_forbidden_response_when_two_factor_is_disabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.show'))
            ->assertForbidden();
    }

    public function test_two_factor_authentication_can_be_enabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('two-factor.enable'));

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNotNull($user->fresh()->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_can_be_disabled()
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('two-factor.disable'));

        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_recovery_codes_can_be_viewed()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('two-factor.enable'));

        $response = $this->actingAs($user)
            ->get(route('two-factor.recovery-codes'));

        $response->assertStatus(200);

        $this->assertNotEmpty($response->json());
        $response->assertJsonCount(8);
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('security.destroy'), [
                'password' => 'Password123!',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('security.show'))
            ->delete(route('security.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('security.show'));

        $this->assertNotNull($user->fresh());
    }
}
