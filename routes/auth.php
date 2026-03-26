<?php

use App\Http\Controllers\Auth\GoogleAuthCallbackController;
use App\Http\Controllers\Auth\GoogleAuthRedirectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SetupAccountController;
use App\Services\LocationService;
use Illuminate\Support\Facades\Route;
use OpenFoodFacts\Laravel\Facades\OpenFoodFacts;

// todo: move to api.php
Route::get('api/countries/{country}/timezones', function ($country) {
    return response()->json(LocationService::getTimezonesForCountry($country));
});

// todo: move to api.php
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

Route::get('auth/google/callback', GoogleAuthCallbackController::class)->name('google.callback');
Route::get('auth/google/redirect', GoogleAuthRedirectController::class)->name('google.redirect');

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store'])->name('login.store');

Route::get('register', [RegisterController::class, 'create'])->name('register');
Route::post('register', [RegisterController::class, 'store'])->name('register.store');

Route::get('setup-account', [SetupAccountController::class, 'create'])->name('setup-account');
Route::post('setup-account', [SetupAccountController::class, 'store'])->middleware('handlePrecognition')->name('setup-account.store');
