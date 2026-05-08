<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\NutritionEntryRequest;
use App\Models\NutritionEntry;
use Inertia\Inertia;

class NutritionEntryController extends Controller
{
    public function index()
    {
        $range = request()->query('range', '7');
        $userNow = now()->inUserTimezone()->startOfDay();

        $firstEntry = NutritionEntry::whereUserId(auth()->id())
            ->oldest('date')
            ->value('date');

        $journeyStart = $firstEntry
            ? now()->parse($firstEntry, $userNow->timezone)->startOfDay()
            : $userNow->copy();

        // 1. Determine Chart Bounds
        $requestedStart = $range === 'all'
            ? $journeyStart->copy()
            : $userNow->copy()->subDays((int)$range - 1);

        $chartStart = $requestedStart->lt($journeyStart) ? $journeyStart->copy() : $requestedStart;

        // 2. Fetch and aggregate entries by date (sum calories per day)
        $chartEntries = NutritionEntry::whereUserId(auth()->id())
            ->where('date', '>=', $chartStart->format('Y-m-d'))
            ->get()
            ->groupBy('date')
            ->map(fn($entries) => $entries->sum('calories'));

        // 3. Generate Chart Points
        $chartData = [];
        $totalDays = $chartStart->diffInDays($userNow);
        $step = ($totalDays > 30) ? (int) ceil($totalDays / 50) : 1;

        $pointer = $chartStart->copy();

        // Loop until we reach the day BEFORE today
        while ($pointer->lt($userNow)) {
            $day = $pointer->format('Y-m-d');

            $chartData[] = [
                'date'     => $pointer->format('M d'),
                'calories' => (int) ($chartEntries[$day] ?? 0), // 0 for days with no entries
            ];

            $pointer->addDays($step);
        }

        // Always include today
        $today = $userNow->format('Y-m-d');
        $chartData[] = [
            'date'     => $userNow->format('M d'),
            'calories' => (int) ($chartEntries[$today] ?? 0),
        ];

        // 4. Entries list (most recent 50, grouped by meal)
        $nutritionEntries = NutritionEntry::whereUserId(auth()->id())
            ->latest('date')
            ->take(50)
            ->get()
            ->map(fn($entry) => [
                'id' => $entry->id,
                'date' => now()->parse($entry->date)->format('F jS, Y'),
                'date_raw' => $entry->date,
                'food_item' => $entry->food_item,
                'calories' => $entry->calories,
                'protein' => $entry->protein,
                'carbohydrates' => $entry->carbohydrates,
                'fats' => $entry->fats,
            ]);

        return Inertia::render('nutrition-entries/Index', [
            'chartData' => $chartData,
            'nutritionEntries' => $nutritionEntries,
            'filters' => ['range' => $range],
            'today' => now()->inUserTimezone()->format('Y-m-d')
        ]);
    }

    public function create()
    {
        return Inertia::render('nutrition-entries/Create');
    }

    public function store(NutritionEntryRequest $request)
    {
        $validated = $request->validated();

        $request->user()->nutritionEntries()->create($validated);

        return to_route('nutrition-entries.index');
    }

    public function show(NutritionEntry $nutritionEntry)
    {
        $this->authorize('view', $nutritionEntry);

        return Inertia::render('nutrition-entries/Show', [
            'nutritionEntry' => $nutritionEntry,
        ]);
    }

    public function edit(NutritionEntry $nutritionEntry)
    {
        $this->authorize('update', $nutritionEntry);

        return Inertia::render('nutrition-entries/Edit', [
            'nutritionEntry' => $nutritionEntry,
        ]);
    }

    public function update(NutritionEntryRequest $request, NutritionEntry $nutritionEntry)
    {
        $this->authorize('update', $nutritionEntry);

        $validated = $request->validated();

        $nutritionEntry->update($validated);

        return to_route('nutrition-entries.index');
    }
}
