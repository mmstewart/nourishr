<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetupWeightGoalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'hasWeightGoal', 'verified'])
    ->name('dashboard');

Route::resource('nutrition-entries', \App\Http\Controllers\NutritionEntryController::class)->middleware(['auth']);

Route::get('setup-weight-goal', [SetupWeightGoalController::class, 'create'])->middleware('auth')->name('setup-weight-goal');
Route::post('setup-weight-goal', [SetupWeightGoalController::class, 'store'])->middleware(['auth', 'handlePrecognition'])->name('setup-weight-goal.store');

Route::resource('weight-entries', \App\Http\Controllers\WeightEntryController::class)->middleware(['auth'])->except('destroy');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
