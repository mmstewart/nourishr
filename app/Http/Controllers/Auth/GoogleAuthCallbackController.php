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
        $googleUser = Socialite::driver('google')->user();

        // $user = User::updateOrCreate([
        //     'id' => $googleUser->getId(),
        // ], [
        //     'name' => $googleUser->getName(),
        //     'email' => $googleUser->getEmail(),
        //     'email_verified_at' => now(),
        // ]);

        return Inertia::location(route('dashboard'));
    }
}
