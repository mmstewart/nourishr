<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\CountryList;
use Inertia\Inertia;

class SetupController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if (auth()->guest()) {
            return Inertia::render('auth/Setup', [
                'step' => 'account'
            ]);
        }

        if (!$user->hasCompletedSetup()) {
            $countries = CountryList::all();

            return Inertia::render('auth/Setup', [
                'countries' => $countries,
                'step' => 'profile',
            ]);
        }

        if ($user->weightGoal()->doesntExist()) {
            return Inertia::render('auth/Setup', [
                'step' => 'weightGoal',
            ]);
        }

        return redirect()->route('dashboard');
    }
}
