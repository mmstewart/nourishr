<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WeightEntry;
use App\Models\WeightGoal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WeightEntryController extends Controller
{
    public function index()
    {
        $range = request()->query('range', '7');
        $userNow = now()->inUserTimezone();

        // 1. Get Goal - Use local timezone for the start_date to prevent shifting to the previous day
        $goal = WeightGoal::where('user_id', auth()->id())->first();

        $initialWeightDate = $goal->start_date;
        $initialWeight = $goal->start_weight;

        // 2. Parse strictly to the User's Timezone
        $journeyStart = now()->parse($initialWeightDate, $userNow->timezone)->startOfDay();

        // 3. Determine requested start and CLAMP
        $requestedStart = ($range === 'all')
            ? $journeyStart->copy()
            : $userNow->copy()->subDays((int)$range - 1)->startOfDay();

        // Clamp: Don't go before the journey started
        $start = $requestedStart->lt($journeyStart) ? $journeyStart->copy() : $requestedStart;

        $entries = WeightEntry::where('user_id', auth()->id())
            ->where('date', '>=', $start->format('Y-m-d'))
            ->get()
            ->keyBy('date');

        // 5. Correct Carry-over Weight
        // Check DB for entries before $start; if none, use the Goal's start_weight
        $dbLastWeight = WeightEntry::where('user_id', auth()->id())
            ->where('date', '<', $start->format('Y-m-d'))
            ->latest('date')
            ->value('weight');

        $lastWeight = $dbLastWeight ? (float) $dbLastWeight : $initialWeight;

        $chartData = [];

        $totalDays = $start->diffInDays($userNow);
        $step = ($totalDays > 30) ? ceil($totalDays / 50) : 1;

        while ($start->lte($userNow)) {
            $day = $start->format('Y-m-d');

            if ($entries->has($day)) {
                $lastWeight = (float) $entries[$day]->weight;
            }

            $chartData[] = [
                'date'   => $start->format('M d'),
                'weight' => $lastWeight,
            ];

            $start->addDays($step);
        }

        return Inertia::render('weight-entries/Index', [
            'chartData'     => $chartData,
            'weightEntries' => $entries->reverse()->values()->take(50),
            'filters'       => ['range' => $range]
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(WeightEntry $weightEntry)
    {
        //
    }

    public function edit(WeightEntry $weightEntry)
    {
        //
    }

    public function update(Request $request, WeightEntry $weightEntry)
    {
        //
    }
}
