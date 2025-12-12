<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PasswordController extends Controller
{
    public function edit()
    {
        return Inertia::render('settings/Password');
    }

    public function update()
    {
        $validated = request()->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::min(10)->numbers()->symbols()],
            'new_password_confirmation' => ['required'],
        ]);

        if (Hash::check(request()->new_password, request()->user()->password)) {
            return back()->withErrors(['new_password' => 'The new password must be different from your current password.']);
        }

        request()->user()->update([
            'new_password' => Hash::make($validated['new_password']),
        ]);

        return back();
    }
}
