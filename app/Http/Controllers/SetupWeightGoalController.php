<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeightRequest;
use App\Models\ActivityLevel;
use App\Models\WeeklyGoal;
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

    public function store(StoreWeightRequest $request)
    {
        $validated = $request->validated();

        $validated['start_weight'] = $validated['current_weight'];
        $validated['start_date'] = now()->inUserTimezone();
        $validated['user_id'] = auth()->id();

        auth()->user()->weightGoal()->create($validated);

        auth()->user()->weightEntries()->create([
            'date' => now()->inUserTimezone(),
            'weight' => $validated['start_weight'],
        ]);

        return Inertia::location(route('dashboard'));
    }
}
