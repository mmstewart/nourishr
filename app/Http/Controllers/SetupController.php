<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WeeklyGoal;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SetupController extends Controller
{
    public function create()
    {
        $weeklyGoals = WeeklyGoal::all();

        return Inertia::render('Setup', [
            'weeklyGoals' => $weeklyGoals,
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'weekly_goal_id' => ['required', 'integer'],
            'current_weight' => ['required', 'decimal:0,1'],
            'goal_weight' => ['required', 'decimal:0,1'],
        ]);
        $validated['start_weight'] = $validated['current_weight'];
        $validated['start_date'] = now();
        $validated['user_id'] = Auth::id();

        Auth::user()->weightGoals()->create($validated);

        return Inertia::location(route('dashboard'));
    }
}
