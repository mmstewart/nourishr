<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreSetupAccountRequest;
use App\Models\User;
use App\Support\CountryList;
use Illuminate\Support\Facades\Auth;
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

    public function store(StoreSetupAccountRequest $request)
    {
        $validated = $request->validated();

        $user = User::create($validated);

        Auth::login($user);

        return Inertia::location(route('dashboard'));
    }
}
