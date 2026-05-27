<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNutritionEntryRequest;
use App\Http\Requests\UpdateNutritionEntryRequest;
use App\Models\NutritionEntry;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NutritionEntryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', NutritionEntry::class);

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
            ->groupBy('date')
            ->map(fn($entries, $date) => [
                'date' => now()->parse($date)->format('F jS, Y'),
                'date_raw' => $date,
                'entries' => $entries->map(fn($entry) => [
                    'id' => $entry->id,
                    'meal_type' => $entry->meal_type,
                    'food_item' => ucfirst(strtolower($entry->food_item)),
                    'calories' => $entry->calories,
                    'protein' => $entry->protein,
                    'carbohydrates' => $entry->carbohydrates,
                    'fats' => $entry->fats,
                    'time' => $entry->created_at->inUserTimezone()->format('g:i A'),
                ])->values(),
                'total_calories' => $entries->sum('calories'),
            ])
            ->values();

        return Inertia::render('nutrition-entries/Index', [
            'chartData' => $chartData,
            'nutritionEntries' => $nutritionEntries,
            'filters' => ['range' => $range],
            'today' => now()->inUserTimezone()->format('Y-m-d')
        ]);
    }

    public function create()
    {
        $this->authorize('create', NutritionEntry::class);

        return Inertia::render('nutrition-entries/Create');
    }

    public function store(StoreNutritionEntryRequest $request)
    {
        // $this->authorize('create', NutritionEntry::class);

        // $validated = $request->validated();

        // $now = now();

        // $request->user()->nutritionEntries()->insert(
        //     collect($validated['entries'])->map(fn($entry) => [
        //         ...$entry,
        //         'date'       => $validated['date'],
        //         'user_id'    => $request->user()->id,
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ])->toArray()
        // );

        DB::table('nutrition_entries')->insert([
            'food_item'     => 'test',
            'meal_type'     => 'lunch',
            'calories'      => 100,
            'protein'       => 10,
            'carbohydrates' => 10,
            'fats'          => 5,
            'quantity'      => 1,
            'date'          => now()->toDateString(),
            'user_id'       => $request->user()->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function show(NutritionEntry $nutritionEntry)
    {
        $this->authorize('view', $nutritionEntry);

        $formattedEntry = [
            'id' => $nutritionEntry->id,
            'meal_type' => $nutritionEntry->meal_type,
            'food_item' => ucfirst(strtolower($nutritionEntry->food_item)),
            'calories' => $nutritionEntry->calories,
            'protein' => $nutritionEntry->protein,
            'carbohydrates' => $nutritionEntry->carbohydrates,
            'fats' => $nutritionEntry->fats,
            'serving_size' => $nutritionEntry->serving_size,
            'quantity' => $nutritionEntry->quantity,
            'date' => now()->parse($nutritionEntry->date)->inUserTimezone()->format('F jS, Y'),
            'time' => $nutritionEntry->created_at->inUserTimezone()->format('g:i A'),
        ];

        return Inertia::render('nutrition-entries/Show', [
            'nutritionEntry' => $formattedEntry,
        ]);
    }

    public function edit(NutritionEntry $nutritionEntry)
    {
        $this->authorize('update', $nutritionEntry);

        $servingOptions = $nutritionEntry->getServingOptions();

        return Inertia::render('nutrition-entries/Edit', [
            'nutritionEntry' => $nutritionEntry,
            'servingOptions' => $servingOptions,
        ]);
    }

    public function update(UpdateNutritionEntryRequest $request, NutritionEntry $nutritionEntry)
    {
        $this->authorize('update', $nutritionEntry);

        $validated = $request->validated();

        $nutritionEntry->update($validated);

        return to_route('nutrition-entries.show', $nutritionEntry);
    }

    public function destroy(NutritionEntry $nutritionEntry)
    {
        $this->authorize('delete', $nutritionEntry);

        $nutritionEntry->delete();

        return to_route('nutrition-entries.index');
    }
}
