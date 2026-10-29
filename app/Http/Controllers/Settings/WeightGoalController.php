<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateWeightGoalRequest;
use App\Models\ActivityLevel;
use App\Models\WeeklyGoal;
use Inertia\Inertia;

class WeightGoalController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->load('weightGoal');

        $weeklyGoals = WeeklyGoal::availableFor($user)->get();

        $activityLevels = ActivityLevel::all();

        return Inertia::render('settings/WeightGoal', [
            'weeklyGoals' => $weeklyGoals,
            'activityLevels' => $activityLevels,
            'user' => $user,
        ]);
    }

    public function update(UpdateWeightGoalRequest $request)
    {
        $validated = $request->validated();

        $request->user()->weightGoal()->update($validated);

        return back();
    }
}
