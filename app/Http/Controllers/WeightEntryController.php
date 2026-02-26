<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightEntryRequest;
use App\Models\WeightEntry;
use App\Models\WeightGoal;
use Inertia\Inertia;

class WeightEntryController extends Controller
{
    public function index()
    {
        $range = request()->query('range', '7');
        $userNow = now()->inUserTimezone()->startOfDay();

        $weightGoal = WeightGoal::where('user_id', auth()->id())->firstOrFail();

        $journeyStart = now()->parse($weightGoal->start_date, $userNow->timezone)->startOfDay();

        // 1. Determine Chart Bounds
        $requestedStart = $range === 'all'
            ? $journeyStart->copy()
            : $userNow->copy()->subDays((int)$range - 1);

        $chartStart = $requestedStart->lt($journeyStart) ? $journeyStart->copy() : $requestedStart;

        // 2. Fetch data
        $chartEntries = WeightEntry::where('user_id', auth()->id())
            ->where('date', '>=', $chartStart->format('Y-m-d'))
            ->get()
            ->keyBy('date');

        $lastWeight = WeightEntry::where('user_id', auth()->id())
            ->where('date', '<', $chartStart->format('Y-m-d'))
            ->latest('date')
            ->value('weight') ?? $weightGoal->start_weight;

        // 3. Generate Chart Points
        $chartData = [];
        $totalDays = $chartStart->diffInDays($userNow);
        $step = ($totalDays > 30) ? (int) ceil($totalDays / 50) : 1;

        $pointer = $chartStart->copy();

        // Loop until we reach the day BEFORE today
        while ($pointer->lt($userNow)) {
            $day = $pointer->format('Y-m-d');

            if ($chartEntries->has($day)) {
                $lastWeight = (float) $chartEntries[$day]->weight;
            }

            $chartData[] = [
                'date'   => $pointer->format('M d'),
                'weight' => $lastWeight,
            ];

            $pointer->addDays($step);
        }

        if ($chartEntries->has($userNow->format('Y-m-d'))) {
            $lastWeight = (float) $chartEntries[$userNow->format('Y-m-d')]->weight;
        }

        $chartData[] = [
            'date'   => $userNow->format('M d'),
            'weight' => $lastWeight,
        ];

        // 5. Cleanup variables for return
        $weightEntries = WeightEntry::where('user_id', auth()->id())
            ->latest('date')
            ->take(50)
            ->get()
            ->map(fn($entry) => [
                'id' => $entry->id,
                'date' => now()->parse($entry->date)->format('F jS, Y'),
                'weight' => $entry->weight,
            ]);

        return Inertia::render('weight-entries/Index', [
            'chartData' => $chartData,
            'weightEntries' => $weightEntries,
            'filters' => ['range' => $range],
        ]);
    }

    public function create()
    {
        return Inertia::render('weight-entries/Create');
    }

    public function store(WeightEntryRequest $request)
    {
        $validated = $request->validated();

        $request->user()->weightEntries()->create($validated);

        return to_route('weight-entries.index');
    }

    public function edit(WeightEntry $weightEntry)
    {
        return Inertia::render('weight-entries/Edit', [
            'weightEntry' => $weightEntry,
        ]);
    }

    public function update(WeightEntryRequest $request, WeightEntry $weightEntry)
    {
        $validated = $request->validated();

        $weightEntry->update($validated);

        return to_route('weight-entries.index');
    }
}
