<?php

use App\Http\Controllers\CoachController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FoodFactsController;
use App\Http\Controllers\NutritionEntryController;
use App\Http\Controllers\WeightEntryController;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::get('countries/{country}/timezones', function ($country) {
    abort_if(!preg_match('/^[A-Z]{2}$/', $country), 422);

    return response()->json(LocationService::getTimezonesForCountry($country));
});

Route::get('contact', fn () => Inertia::render('Contact'))->name('contact');
Route::post('contact', ContactController::class)->middleware('throttle:6,1')->name('contact.store');
Route::get('privacy-policy', fn() => Inertia::render('PrivacyPolicy'))->name('privacy-policy');
Route::get('terms-of-use', fn() => Inertia::render('TermsOfUse'))->name('terms-of-use');

Route::middleware(['auth', 'setup.incomplete', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('feedback', fn() => Inertia::render('Feedback'))->name('feedback');
    Route::post('feedback', FeedbackController::class)->middleware('throttle:6,1')->name('feedback.store');
    Route::get('coach', [CoachController::class, 'index'])->name('coach');
    Route::post('coach', [CoachController::class, 'ask'])->middleware('throttle:10,1')->name('coach.ask');
    Route::get('foodfacts', FoodFactsController::class);
    Route::resource('nutrition-entries', NutritionEntryController::class);
    Route::resource('weight-entries', WeightEntryController::class);
});

Route::post('impersonate/{user}', function (Request $request, User $user) {
    Log::warning('Admin impersonation', ['admin_id' => $request->user()->id, 'target_id' => $user->id, 'ip' => $request->ip()]);

    Auth::login($user);
    $request->session()->regenerate();

    return to_route('dashboard');
})->middleware(['auth', 'can:impersonate,user'])->name('impersonate');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
