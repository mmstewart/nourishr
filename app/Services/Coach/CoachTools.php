<?php

namespace App\Services\Coach;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only tools the coach can call to look at one user's own Nourishr data.
 *
 * Every tool runs as the user this object was built for. The model never
 * passes a user ID, so it can't see anyone else's data.
 */
class CoachTools
{
    /** Longest date range one tool call can read. */
    public const MAX_RANGE_DAYS = 31;

    /** Default range when the model doesn't give dates. */
    public const DEFAULT_RANGE_DAYS = 7;

    public const MAX_FOOD_RESULTS = 5;

    public function __construct(private User $user) {}

    /**
     * Provider-neutral tool definitions (name, description, JSON schema).
     */
    public static function definitions(): array
    {
        $dateRange = [
            'type' => 'object',
            'properties' => [
                'start_date' => ['type' => 'string', 'description' => 'First day to include, YYYY-MM-DD. Defaults to 7 days ago.'],
                'end_date' => ['type' => 'string', 'description' => 'Last day to include, YYYY-MM-DD. Defaults to today.'],
            ],
        ];

        return [
            [
                'name' => 'get_nutrition_log',
                'description' => 'Get the user\'s logged meals and daily totals (calories, protein, carbohydrates, fats) for a date range of up to '.self::MAX_RANGE_DAYS.' days.',
                'parameters' => $dateRange,
            ],
            [
                'name' => 'get_weight_history',
                'description' => 'Get the user\'s logged weights (in pounds) for a date range of up to '.self::MAX_RANGE_DAYS.' days.',
                'parameters' => $dateRange,
            ],
            [
                'name' => 'get_goals',
                'description' => 'Get the user\'s weight goal, weekly pace, activity level, and daily calorie and macro targets.',
                'parameters' => ['type' => 'object', 'properties' => new \stdClass],
            ],
            [
                'name' => 'search_food',
                'description' => 'Search the USDA food database. Returns up to '.self::MAX_FOOD_RESULTS.' foods with calories, protein, carbohydrates, and fats per 100g.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Food to search for, e.g. "greek yogurt".'],
                    ],
                    'required' => ['query'],
                ],
            ],
        ];
    }

    /**
     * Run a tool and return a JSON-serializable result. Errors come back as
     * {"error": "..."} so the model can explain them instead of the request failing.
     */
    public function run(string $name, array $args): array
    {
        try {
            return match ($name) {
                'get_nutrition_log' => $this->nutritionLog($args),
                'get_weight_history' => $this->weightHistory($args),
                'get_goals' => $this->goals(),
                'search_food' => $this->searchFood($args),
                default => ['error' => "Unknown tool \"{$name}\"."],
            };
        } catch (Throwable $e) {
            Log::warning('Coach tool failed', ['tool' => $name, 'error' => $e->getMessage()]);

            return ['error' => 'This data is unavailable right now.'];
        }
    }

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now($this->user->timezone ?: config('app.timezone'))->startOfDay();
    }

    /**
     * Turn the model's dates into a safe range: valid dates, nothing after
     * today, start before end, and at most MAX_RANGE_DAYS long.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: ?string}
     */
    private function dateRange(array $args): array
    {
        $today = $this->today();
        $note = null;

        $end = $this->parseDate($args['end_date'] ?? null) ?? $today;
        $start = $this->parseDate($args['start_date'] ?? null) ?? $end->subDays(self::DEFAULT_RANGE_DAYS - 1);

        if ($end->greaterThan($today)) {
            $end = $today;
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        if ($start->diffInDays($end) >= self::MAX_RANGE_DAYS) {
            $start = $end->subDays(self::MAX_RANGE_DAYS - 1);
            $note = 'Range shortened to the last '.self::MAX_RANGE_DAYS.' days.';
        }

        return [$start, $end, $note];
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts)) {
            return null;
        }

        // Reject impossible dates like 2026-13-45, which Carbon would roll over.
        if (! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value, $this->user->timezone ?: config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }

    private function nutritionLog(array $args): array
    {
        [$start, $end, $note] = $this->dateRange($args);

        $entries = $this->user->nutritionEntries()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get(['date', 'meal_type', 'food_item', 'calories', 'protein', 'carbohydrates', 'fats']);

        $days = $entries->groupBy(fn ($entry) => CarbonImmutable::parse($entry->date)->toDateString())
            ->map(fn ($dayEntries, $date) => [
                'date' => $date,
                'totals' => [
                    'calories' => (int) $dayEntries->sum('calories'),
                    'protein_g' => round((float) $dayEntries->sum('protein'), 1),
                    'carbohydrates_g' => round((float) $dayEntries->sum('carbohydrates'), 1),
                    'fats_g' => round((float) $dayEntries->sum('fats'), 1),
                ],
                'meals' => $dayEntries->map(fn ($entry) => [
                    'meal' => $entry->meal_type,
                    'food' => $entry->food_item,
                    'calories' => (int) $entry->calories,
                    'protein_g' => (float) $entry->protein,
                    'carbohydrates_g' => (float) $entry->carbohydrates,
                    'fats_g' => (float) $entry->fats,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return array_filter([
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days_logged' => count($days),
            'days' => $days,
            'note' => $note,
        ], fn ($value) => $value !== null);
    }

    private function weightHistory(array $args): array
    {
        [$start, $end, $note] = $this->dateRange($args);

        $weights = $this->user->weightEntries()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get(['date', 'weight'])
            ->map(fn ($entry) => [
                'date' => CarbonImmutable::parse($entry->date)->toDateString(),
                'weight_lbs' => (float) $entry->weight,
            ])
            ->all();

        return array_filter([
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'entries' => $weights,
            'note' => $note,
        ], fn ($value) => $value !== null);
    }

    private function goals(): array
    {
        $goal = $this->user->weightGoal()->with(['weeklyGoal', 'activityLevel'])->first();

        if (! $goal) {
            return ['error' => 'The user has not set a weight goal yet.'];
        }

        return [
            'start_weight_lbs' => (float) $goal->start_weight,
            'current_weight_lbs' => (float) $goal->current_weight,
            'goal_weight_lbs' => (float) $goal->goal_weight,
            'weekly_goal' => $goal->weeklyGoal?->name,
            'activity_level' => $goal->activityLevel?->name,
            'daily_targets' => [
                'calories' => (int) $goal->target_calories,
                'protein_g' => (int) $goal->protein_grams,
                'carbohydrates_g' => (int) $goal->carb_grams,
                'fats_g' => (int) $goal->fat_grams,
            ],
            'maintenance_calories' => (int) $goal->maintenance_calories,
        ];
    }

    private function searchFood(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));

        if ($query === '' || mb_strlen($query) > 100) {
            return ['error' => 'Give a food name of 1 to 100 characters.'];
        }

        $response = Http::timeout(10)->get('https://api.nal.usda.gov/fdc/v1/foods/search', [
            'query' => $query,
            'pageSize' => self::MAX_FOOD_RESULTS,
            'api_key' => config('services.usda.key'),
        ]);

        if ($response->failed()) {
            return ['error' => 'Food search is unavailable right now.'];
        }

        $nutrient = fn (array $food, string $name) => collect($food['foodNutrients'] ?? [])
            ->firstWhere('nutrientName', $name)['value'] ?? null;

        return [
            'query' => $query,
            'per' => '100g',
            'foods' => collect($response->json('foods', []))
                ->take(self::MAX_FOOD_RESULTS)
                ->map(fn ($food) => [
                    'name' => $food['description'] ?? 'Unknown',
                    'brand' => $food['brandOwner'] ?? null,
                    'calories' => $nutrient($food, 'Energy'),
                    'protein_g' => $nutrient($food, 'Protein'),
                    'carbohydrates_g' => $nutrient($food, 'Carbohydrate, by difference'),
                    'fats_g' => $nutrient($food, 'Total lipid (fat)'),
                ])
                ->values()
                ->all(),
        ];
    }
}
