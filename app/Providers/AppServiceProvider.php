<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // https://medium.com/@segunibidokun/difference-between-carbon-and-carbonimmutable-0c9689518773
        CarbonImmutable::macro('inUserTimezone', function () {
            return $this->tz(auth()->user()?->timezone ?? config('app.timezone_display'));
        });
    }
}
