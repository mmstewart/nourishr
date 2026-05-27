<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FoodFactsController;
use App\Http\Controllers\NutritionEntryController;
use App\Http\Controllers\WeightEntryController;
use App\Services\LocationService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/debug-db', function () {
    return response()->json([
        'db_host' => explode('.', env('DB_HOST'), 2)[1] ?? 'unknown', // shows domain without subdomain
        'db_connection' => env('DB_CONNECTION'),
        'db_port' => env('DB_PORT'),
    ]);
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'isLoggedIn' => auth()->check(),
    ]);
})->name('home');

Route::get('countries/{country}/timezones', function ($country) {
    abort_if(!preg_match('/^[A-Z]{2}$/', $country), 422);

    return response()->json(LocationService::getTimezonesForCountry($country));
});

Route::get('contact', function () {
    return Inertia::render('Contact', [
        'isLoggedIn' => auth()->check(),
    ]);
})->name('contact');

Route::post('contact', ContactController::class)->middleware('throttle:6,1')->name('contact.store');
Route::get('privacy-policy', fn() => Inertia::render('PrivacyPolicy'))->name('privacy-policy');
Route::get('terms-of-use', fn() => Inertia::render('TermsOfUse'))->name('terms-of-use');

Route::middleware(['auth', 'setup.incomplete', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('feedback', fn() => Inertia::render('Feedback'))->name('feedback');
    Route::post('feedback', FeedbackController::class)->middleware('throttle:6,1')->name('feedback.store');
    Route::get('foodfacts', FoodFactsController::class);
    Route::resource('nutrition-entries', NutritionEntryController::class);
    Route::resource('weight-entries', WeightEntryController::class);
});

Route::get('impersonate/{user}', function ($user) {
    abort_unless(auth()->user()?->is_admin, 403);

    auth()->loginUsingId($user);

    return redirect()->route('dashboard');
})->middleware('auth')->name('impersonate');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
