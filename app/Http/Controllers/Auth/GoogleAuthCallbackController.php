<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthCallbackController extends Controller
{
    public function __invoke()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate([
                'google_id' => $googleUser->getId(),
            ], [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
            ]);

            Auth::login($user);

            return redirect()->route('setup');
        } catch (\Exception $e) {
            dd([
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
