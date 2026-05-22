<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthRedirectController extends Controller
{
    public function __invoke()
    {
        // Redirect the user to Google's OAuth consent screen
        return Socialite::driver('google')->stateless()->redirect();
    }
}
