<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLevel;
use App\Models\WeeklyGoal;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SetupWeightGoalController extends Controller
{
    public function create()
    {
        $weeklyGoals = WeeklyGoal::all();

        $activityLevels = ActivityLevel::all();

        return Inertia::render('SetupWeight', [
            'weeklyGoals' => $weeklyGoals,
            'activityLevels' => $activityLevels,
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'weekly_goal_id' => ['required', 'integer'],
            'activity_level_id' => ['required', 'integer'],
            'current_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
            'goal_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
        ]);
        $validated['start_weight'] = $validated['current_weight'];
        $validated['start_date'] = now();
        $validated['user_id'] = Auth::id();

        Auth::user()->weightGoal()->create($validated);

        return Inertia::location(route('dashboard'));
    }
}
