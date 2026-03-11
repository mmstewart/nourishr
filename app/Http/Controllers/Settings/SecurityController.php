<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SecurityRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class SecurityController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    public function show(SecurityRequest $request)
    {
        $request->ensureStateIsValid();

        return Inertia::render('settings/Security', [
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }
}
