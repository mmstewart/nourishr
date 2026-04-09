<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use Inertia\Inertia;

class PasswordController extends Controller
{
    public function edit()
    {
        return Inertia::render('settings/Password');
    }

    public function update(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        // todo, test for google and non google users
        $request->user()->update([
            'password' => $validated['new_password'],
        ]);

        return back();
    }
}
