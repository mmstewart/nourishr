<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user()->load([
            'weightGoal',
            'weightEntries' => fn($query) => $query->latest('date')->limit(1),
        ]);

        $latestWeight = $user->weightEntries->first()?->weight ?? $user->weightGoal->start_weight;

        return Inertia::render('Dashboard', [
            'user' => $user,
            'latestWeight' => $latestWeight,
        ]);
    }
}
