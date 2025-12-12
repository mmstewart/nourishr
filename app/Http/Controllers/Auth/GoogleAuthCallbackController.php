<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthCallbackController extends Controller
{
    public function __invoke()
    {
        $google_user = Socialite::driver('google')->user();

        $user = User::updateOrCreate([
            'id' => $google_user->getId(),
        ], [
            'name' => $google_user->getName(),
            'email' => $google_user->getEmail(),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return Inertia::location(route('dashboard'));
    }
}
