<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NutritionEntryController;
use App\Http\Controllers\SetupWeightGoalController;
use App\Http\Controllers\WeightEntryController;
use App\Services\LocationService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use OpenFoodFacts\Laravel\Facades\OpenFoodFacts;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->middleware('guest')->name('home');

Route::get('contact', fn() => Inertia::render('Contact'))->name('contact');
Route::post('contact', ContactController::class)->middleware('throttle:6,1')->name('contact.store');

Route::redirect('dashboard', '/');

Route::middleware('auth')->group(function () {
    Route::get('api/countries/{country}/timezones', function ($country) {
        abort_if(!preg_match('/^[A-Z]{2}$/', $country), 422);

        return response()->json(LocationService::getTimezonesForCountry($country));
    });

    // todo: make work
    Route::get('api/foodfacts', function () {
        $food_item = request()->query('food_item');
        $type = request()->query('type', 'search');

        if (!$food_item) {
            return response()->json(['error' => 'Missing food item'], 400);
        }

        try {
            if ($type === 'barcode') {
                $result = OpenFoodFacts::barcode($food_item);
                return response()->json($result);
            }

            $collection = OpenFoodFacts::find($food_item);
            return response()->json([
                'products' => $collection->values()->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    });

    Route::get('dashboard', DashboardController::class)
        ->middleware('hasWeightGoal', 'verified')
        ->name('dashboard');

    Route::get('feedback', fn() => Inertia::render('Feedback'))->name('feedback');
    Route::post('feedback', FeedbackController::class)->middleware('throttle:6,1')->name('feedback.store');

    Route::resource('nutrition-entries', NutritionEntryController::class)->except('destroy');

    Route::get('setup-weight-goal', [SetupWeightGoalController::class, 'create'])->name('setup-weight-goal');
    Route::post('setup-weight-goal', [SetupWeightGoalController::class, 'store'])->middleware('handlePrecognition')->name('setup-weight-goal.store');

    Route::resource('weight-entries', WeightEntryController::class)->except('show', 'destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
