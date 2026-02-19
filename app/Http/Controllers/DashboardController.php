<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $tz = now()->inUserTimezone();
        $startDate = $tz->copy()->subDays(6)->startOfDay();

        $user = auth()->user()->load([
            'weightGoal',
            'weightEntries' => fn($q) => $q->where('date', '>=', $startDate->format('Y-m-d'))->orderBy('date'),
        ]);

        $allWeights = $user->weightEntries()->orderBy('date')->get();

        $latestWeight = $allWeights->last()?->weight ?? $user->weightGoal->start_weight;

        $chartWeightTracker = $allWeights->where('date', '<', $startDate->format('Y-m-d'))->last()?->weight ?? $user->weightGoal->start_weight;

        $nutritionEntries = $user->nutritionEntries->groupBy('date');

        $weightEntries = $allWeights->keyBy('date');

        $chartData = collect($startDate->daysUntil($tz))->map(function ($date) use ($nutritionEntries, $weightEntries, &$chartWeightTracker) {
            $dateKey = $date->format('Y-m-d');

            $chartWeightTracker = $weightEntries->get($dateKey)->weight ?? $chartWeightTracker;

            return [
                'date'     => $date->format('M d'),
                'calories' => (int) ($nutritionEntries->get($dateKey)?->sum('calories')),
                'weight'   => (float) $chartWeightTracker,
            ];
        });

        return Inertia::render('Dashboard', [
            'user' => $user,
            'chartData' => $chartData,
            'latestWeight' => $latestWeight,
        ]);
    }
}
