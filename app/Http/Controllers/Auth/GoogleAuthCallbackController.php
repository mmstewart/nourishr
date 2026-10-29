<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthCallbackController extends Controller
{
    public function __invoke()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable $e) {
            // Expired or reused codes, denied consent, or Google being unreachable.
            Log::warning('Google sign-in failed', ['error' => $e->getMessage()]);

            return to_route('login')->withErrors([
                'google' => 'Google sign-in didn\'t work. Please try again.',
            ]);
        }

        $email = $googleUser->getEmail();
        $emailVerified = (bool) ($googleUser->user['email_verified'] ?? false);

        if (blank($email)) {
            return to_route('login')->withErrors([
                'google' => 'Your Google account didn\'t share an email address.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $existing = User::where('email', $email)->first();

            if ($existing && ! $emailVerified) {
                // Only link when Google has verified the email; otherwise anyone could
                // claim an account by creating a Google account with that address.
                return to_route('login')->withErrors([
                    'google' => 'An account with this email already exists. Sign in with your password instead.',
                ]);
            }

            $user = $existing
                ? $this->linkExistingAccount($existing, $googleUser->getId())
                : User::create([
                    'google_id' => $googleUser->getId(),
                    'name' => $googleUser->getName() ?: $email,
                    'email' => $email,
                    'email_verified_at' => $emailVerified ? now() : null,
                ]);
        }

        Auth::login($user);

        return redirect()->route('setup');
    }

    /**
     * Someone who signed up with a password and later uses "Continue with
     * Google" with the same (Google-verified) email gets the two linked
     * instead of an error.
     */
    private function linkExistingAccount(User $user, string $googleId): User
    {
        // If the existing account never verified its email, its password may
        // have been set by someone who doesn't own the address. Remove it; the
        // owner can set a new one from settings.
        if (! $user->hasVerifiedEmail()) {
            $user->password = null;
            $user->email_verified_at = now();
        }

        $user->google_id = $googleId;
        $user->save();

        return $user;
    }
}
