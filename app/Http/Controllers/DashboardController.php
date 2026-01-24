<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $weightGoal = auth()->user()->load('weightGoal')->weightGoal;

        return Inertia::render('Dashboard', [
            'weightGoal' => $weightGoal,
        ]);
    }
}
