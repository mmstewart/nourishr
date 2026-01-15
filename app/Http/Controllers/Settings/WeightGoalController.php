<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WeeklyGoal;

use Inertia\Inertia;

class WeightGoalController extends Controller
{
    public function edit()
    {
        $weeklyGoals = WeeklyGoal::all();
        $weightGoal = auth()->user()->load('weightGoal')->weightGoal;

        return Inertia::render('settings/WeightGoal', [
            'weeklyGoals' => $weeklyGoals,
            'weightGoal' => $weightGoal,
        ]);
    }

    public function update()
    {
        $validated = request()->validate([
            'weekly_goal_id' => ['required', 'integer'],
            'current_weight' => ['required', 'decimal:0,1'],
            'goal_weight' => ['required', 'decimal:0,1'],
        ]);

        request()->user()->weightGoal()->update($validated);

        return back();
    }
}
