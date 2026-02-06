<?php

namespace App\Http\Controllers;

use App\Models\NutritionEntry;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user()->load(['weightGoal', 'nutritionEntries' => function ($query) {
            $query->where('date', now()->inUserTimezone()->format('Y-m-d'));
        }]);

        // $weeklyEntries = NutritionEntry::where('user_id', $user->id)
        //     ->where('date', '>=', now()->inUserTimezone()->subDays(6)->format('Y-m-d'))
        //     ->get()
        //     ->groupBy('date');

        // $period = now()->inUserTimezone()->subDays(6)->daysUntil(now()->inUserTimezone());

        // $goal = $user->weightGoal;
        // $maintenance = $goal->maintenance_calories;

        // $chartData = collect($period->toArray())->map(function ($date) use ($weeklyEntries, $user, $maintenance) {
        //     $dateKey = $date->format('Y-m-d');
        //     $formattedDate = $date->format('M d, Y');

        //     $daySum = $weeklyEntries->has($dateKey)
        //         ? $weeklyEntries->get($dateKey)->sum('calories')
        //         : 0;

        //     $netChange = $daySum > 0 ? ($daySum - $maintenance) / 3500 : 0;

        //     return [
        //         'day'      => $date->format('D'),
        //         'date'     => $formattedDate,
        //         'weight'   => round((float) $user->weightGoal->current_weight + $netChange, 2),
        //         'calories' => $daySum,
        //         'netChange' => $netChange,
        //     ];
        // });

        // dd($chartData->toArray());

        return Inertia::render('Dashboard', [
            'user' => $user,
            'chartData' => $chartData,
        ]);
    }
}
