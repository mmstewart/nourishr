<?php

namespace Tests\Feature\Auth;

use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $email, string $id = 'google-123', bool $verified = true): void
    {
        $googleUser = (new SocialiteUser)
            ->setRaw(['email_verified' => $verified])
            ->map(['id' => $id, 'name' => 'Google User', 'email' => $email]);

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function fakeGoogleFailure(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andThrow(new RuntimeException('invalid_grant'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function visitCallback()
    {
        return $this->get('/auth/google/callback?code=test-code&scope=email');
    }

    public function test_new_google_user_is_created_without_a_password()
    {
        $this->fakeGoogleUser('new@example.com');

        $this->visitCallback()->assertRedirect(route('setup'));

        $user = User::sole();
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_returning_google_user_signs_in_to_the_same_account()
    {
        $user = User::factory()->create(['google_id' => 'google-123', 'email' => 'me@example.com']);
        $this->fakeGoogleUser('me@example.com');

        $this->visitCallback()->assertRedirect(route('setup'));

        $this->assertSame(1, User::count());
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_links_to_an_existing_verified_password_account()
    {
        $user = User::factory()->create(['email' => 'me@example.com', 'email_verified_at' => now()]);
        $originalPassword = $user->password;
        $this->fakeGoogleUser('me@example.com');

        $this->visitCallback()->assertRedirect(route('setup'));

        $user->refresh();
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame($originalPassword, $user->password);
        $this->assertSame(1, User::count());
        $this->assertAuthenticatedAs($user);
    }

    public function test_linking_an_unverified_account_removes_its_password()
    {
        $user = User::factory()->unverified()->create(['email' => 'me@example.com']);
        $this->fakeGoogleUser('me@example.com');

        $this->visitCallback()->assertRedirect(route('setup'));

        $user->refresh();
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_unverified_google_email_does_not_take_over_an_existing_account()
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $this->fakeGoogleUser('me@example.com', verified: false);

        $this->visitCallback()
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertNull($user->refresh()->google_id);
        $this->assertGuest();
    }

    public function test_google_failure_returns_to_login_with_a_message()
    {
        $this->fakeGoogleFailure();

        $this->visitCallback()
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }

    public function test_error_log_saves_request_input_without_failing()
    {
        $this->get('/?code=secret-code&scope=email');

        ErrorLog::record(new RuntimeException('Something broke'));

        $log = ErrorLog::sole();
        $this->assertSame('Something broke', $log->message);
        $this->assertSame(['scope' => 'email'], $log->input);
        $this->assertIsArray($log->stack_trace);
        $this->assertArrayNotHasKey('args', $log->stack_trace[0]);
    }

    public function test_error_log_never_throws_when_saving_fails()
    {
        // Simulate the database failing while recording.
        \Illuminate\Support\Facades\Schema::drop('error_logs');

        ErrorLog::record(new RuntimeException('Something broke'));

        $this->assertTrue(true, 'record() swallowed the failure instead of throwing');
    }
}
