<?php

use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', fn() => Inertia::render('Dashboard'))
    ->middleware(['auth', 'hasGoal', 'verified'])
    ->name('dashboard');

Route::get('setup', [SetupController::class, 'create'])->middleware('auth')->name('setup');
Route::post('setup', [SetupController::class, 'store'])->middleware('auth')->name('setup.store');

require __DIR__.'/settings.php';
require __DIR__ . '/auth.php';
