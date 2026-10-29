<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLevel;
use App\Models\WeeklyGoal;
use App\Support\CountryList;
use Inertia\Inertia;

class SetupController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if (auth()->guest()) {
            return Inertia::render('auth/Setup', [
                'step' => 'account',
            ]);
        }

        if (! $user->hasCompletedProfile()) {
            $countries = CountryList::all();

            return Inertia::render('auth/Setup', [
                'countries' => $countries,
                'step' => 'profile',
            ]);
        }

        if ($user->weightGoal()->doesntExist()) {
            $weeklyGoals = WeeklyGoal::availableFor($user)->get();
            $activityLevels = ActivityLevel::all();

            return Inertia::render('auth/Setup', [
                'step' => 'weightGoal',
                'weeklyGoals' => $weeklyGoals,
                'activityLevels' => $activityLevels,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
