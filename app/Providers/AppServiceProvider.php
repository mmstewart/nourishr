<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Email;

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

        // DNS lookups need internet access and reject example.com, so only run them in production
        Email::defaults(function () {
            $rule = (new Email)->rfcCompliant();

            return app()->isProduction() ? $rule->validateMxRecord() : $rule;
        });

        Gate::define('impersonate', fn (User $admin, User $target) => $admin->is_admin && ! $target->is_admin);
    }
}
