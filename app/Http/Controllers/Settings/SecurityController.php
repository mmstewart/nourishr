<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SecurityRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class SecurityController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        if (!Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            return [];
        }

        if (auth()->check() && is_null(auth()->user()->password)) {
            return [];
        }

        return [new Middleware('password.confirm', only: ['show'])];
    }

    public function show(SecurityRequest $request)
    {
        $request->ensureStateIsValid();

        return Inertia::render('settings/Security', [
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
