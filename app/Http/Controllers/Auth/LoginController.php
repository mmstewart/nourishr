<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreLoginRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/Login');
    }

    public function store(StoreLoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => __('auth.failed')]);
        }

        $user = Auth::user();

        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            Auth::logout();

            $request->session()->put('login.id', $user->id);

            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
