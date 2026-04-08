<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeightGoalRequest;
use App\Models\ActivityLevel;
use App\Models\WeeklyGoal;
use Inertia\Inertia;

class SetupWeightGoalController extends Controller
{
    public function create()
    {
        $weeklyGoals = WeeklyGoal::all();

        $activityLevels = ActivityLevel::all();

        return Inertia::render('SetupWeightGoal', [
            'weeklyGoals' => $weeklyGoals,
            'activityLevels' => $activityLevels,
        ]);
    }

    public function store(StoreWeightGoalRequest $request)
    {
        $validated = $request->validated();

        $date = now()->inUserTimezone();

        $request->user()->weightGoal()->create([
            ...$validated,
            'start_date' => $date,
            'start_weight' => $validated['current_weight'],
        ]);

        $request->user()->weightEntries()->create([
            'date' => $date,
            'weight' => $validated['current_weight'],
        ]);

        return to_route('dashboard');
    }
}
