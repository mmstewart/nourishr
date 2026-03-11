<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NutritionEntryController;
use App\Http\Controllers\SetupWeightGoalController;
use App\Http\Controllers\WeightEntryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)
        ->middleware('hasWeightGoal', 'verified')
        ->name('dashboard');

    Route::get('feedback', fn() => Inertia::render('Feedback'))->name('feedback');
    Route::post('feedback', [FeedbackController::class, 'store'])->middleware('throttle:6,1')->name('feedback.store');

    Route::resource('nutrition-entries', NutritionEntryController::class)->except('destroy');

    Route::get('setup-weight-goal', [SetupWeightGoalController::class, 'create'])->name('setup-weight-goal');
    Route::post('setup-weight-goal', [SetupWeightGoalController::class, 'store'])->middleware('handlePrecognition')->name('setup-weight-goal.store');

    Route::resource('weight-entries', WeightEntryController::class)->except('show', 'destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
