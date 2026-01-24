<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/Register');
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => ['required', 'max:255', 'blasp_check'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'height_inches' => ['required', 'integer'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'gender' => ['required'],
            'password' => ['required', 'confirmed', Password::min(10)->numbers()->symbols()],
            'password_confirmation' => ['required'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'age' => $validated['age'],
            'height_inches' => $validated['height_inches'],
            'gender' => $validated['gender'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return Inertia::location(route('dashboard'));
    }
}
