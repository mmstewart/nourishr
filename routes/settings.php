<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\WeightGoalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/appearance', fn() => Inertia::render('settings/Appearance'))->name('appearance.edit');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'show'])
        ->name('security.show');

    Route::get('settings/weight-goal', [WeightGoalController::class, 'edit'])->name('weight-goal.edit');
    Route::put('settings/weight-goal', [WeightGoalController::class, 'update'])->name('weight-goal.update');
});
