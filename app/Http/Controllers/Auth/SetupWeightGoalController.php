<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeightGoalRequest;
use Illuminate\Support\Facades\DB;

class SetupWeightGoalController extends Controller
{
    public function __invoke(StoreWeightGoalRequest $request)
    {
        $validated = $request->validated();

        $date = now()->inUserTimezone();

        DB::transaction(function () use ($request, $validated, $date) {
            $request->user()->weightGoal()->create([
                'weekly_goal_id' => $validated['weekly_goal_id'],
                'activity_level_id' => $validated['activity_level_id'],
                'start_weight' => $validated['current_weight'],
                'current_weight' => $validated['current_weight'],
                'goal_weight' => $validated['goal_weight'],
                'start_date' => $date,
            ]);

            $request->user()->weightEntries()->create([
                'date' => $date,
                'weight' => $validated['current_weight'],
            ]);
        });

        return to_route('setup');
    }
}
