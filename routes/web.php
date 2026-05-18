<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NutritionEntryController;
use App\Http\Controllers\WeightEntryController;
use App\Services\LocationService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use OpenFoodFacts\Laravel\Facades\OpenFoodFacts;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'isLoggedIn' => auth()->check(),
    ]);
})->name('home');

Route::get('contact', function () {
    return Inertia::render('Contact', [
        'isLoggedIn' => auth()->check(),
    ]);
})->name('contact');
Route::post('contact', ContactController::class)->middleware('throttle:6,1')->name('contact.store');

Route::middleware('auth')->group(function () {
    Route::get('api/countries/{country}/timezones', function ($country) {
        abort_if(!preg_match('/^[A-Z]{2}$/', $country), 422);

        return response()->json(LocationService::getTimezonesForCountry($country));
    });

    Route::get('impersonate/{user}', function ($user) {
        abort_unless(auth()->user()?->is_admin, 403);

        auth()->loginUsingId($user);

        return redirect()->route('dashboard');
    })->name('impersonate');
});

Route::middleware(['auth', 'setup.incomplete', 'verified'])->group(function () {
    // todo: make work
    Route::get('api/foodfacts', function () {
        $food_item = request()->query('food_item');
        $type = request()->query('type', 'search');

        if (!$food_item) {
            return response()->json(['error' => 'Missing food item'], 400);
        }

        try {
            if ($type === 'barcode') {
                // $response = Http::withHeaders([
                //     'User-Agent' => 'Nourishr/1.0 (mmsstewart20@gmail.com)',
                // ])->get("https://world.openfoodfacts.org/api/v2/product/{$food_item}", [
                //     'fields' => 'product_name,nutriments,brands,image_url',
                // ]);

                // return response()->json($response->json()['product'] ?? []);
            }

            $response = Http::get('https://api.nal.usda.gov/fdc/v1/foods/search', [
                'query'    => $food_item,
                'pageSize' => 20,
                'api_key'  => env('USDA_API_SECRET'),
            ]);

            return response()->json($response->json());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('feedback', fn() => Inertia::render('Feedback'))->name('feedback');
    Route::post('feedback', FeedbackController::class)->middleware('throttle:6,1')->name('feedback.store');

    Route::resource('nutrition-entries', NutritionEntryController::class);

    Route::resource('weight-entries', WeightEntryController::class);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
