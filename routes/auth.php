<?php

use App\Http\Controllers\Auth\GoogleAuthCallbackController;
use App\Http\Controllers\Auth\GoogleAuthRedirectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SetupAccountController;
use App\Http\Controllers\Auth\SetupController;
use App\Http\Controllers\Auth\SetupProfileController;
use App\Http\Controllers\Auth\SetupWeightGoalController;
use Illuminate\Support\Facades\Route;

Route::middleware('setup.complete')->group(function () {
    Route::get('auth/google/callback', GoogleAuthCallbackController::class)->name('google.callback');
    Route::get('auth/google/redirect', GoogleAuthRedirectController::class)->name('google.redirect');

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('setup', SetupController::class)->name('setup');
    Route::post('setup-account', SetupAccountController::class)->middleware('handlePrecognition')->name('setup.account');
    Route::post('setup-profile', SetupProfileController::class)->middleware('handlePrecognition')->name('setup.profile');
    Route::post('setup-weight-goal', SetupWeightGoalController::class)->middleware('handlePrecognition')->name('setup.weight-goal');
});
