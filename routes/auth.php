<?php

use App\Http\Controllers\Auth\GoogleAuthCallbackController;
use App\Http\Controllers\Auth\GoogleAuthRedirectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SetupAccountController;
use App\Services\LocationService;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('api/countries/{country}/timezones', function ($country) {
    return response()->json(LocationService::getTimezonesForCountry($country));
});

Route::get('auth/google/callback', GoogleAuthCallbackController::class)->name('google.callback');
Route::get('auth/google/redirect', GoogleAuthRedirectController::class)->name('google.redirect');

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store'])->name('login.store');

Route::get('register', [RegisterController::class, 'create'])->name('register');
Route::post('register', [RegisterController::class, 'store'])->name('register.store');

Route::get('setup-account', [SetupAccountController::class, 'create'])->name('setup-account');
Route::post('setup-account', [SetupAccountController::class, 'store'])->middleware([HandlePrecognitiveRequests::class])->name('setup-account.store');
