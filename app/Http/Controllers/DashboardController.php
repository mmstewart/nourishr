<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // todo add, timezone handling when registering
        $user = auth()->user()->load([
            'weightGoal.activityLevel',
            'nutritionEntries' => function ($query) {
                $query->whereDate('date', now()->format('Y-m-d'));
            }
        ]);

        return Inertia::render('Dashboard', [
            'user' => $user,
        ]);
    }
}
