<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\User;
use App\Support\CountryList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SetupAccountController extends Controller
{
    public function create()
    {
        $countries = CountryList::all();

        return Inertia::render('auth/SetupAccount', [
            'countries' => $countries,
        ]);
    }

    public function store(StoreAccountRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'age' => $validated['age'],
            'height_inches' => $validated['height_inches'],
            'gender' => $validated['gender'],
            'country_code' => $validated['country_code'],
            'timezone' => $validated['timezone'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return Inertia::location(route('dashboard'));
    }
}
