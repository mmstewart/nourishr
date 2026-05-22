<?php

use App\Http\Middleware\EnsureProfileIsSetup;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfSetupComplete;
use App\Http\Middleware\RestrictToNorthAmerica;
use App\Models\ErrorLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'handlePrecognition' => HandlePrecognitiveRequests::class,

            'setup.complete' => RedirectIfSetupComplete::class,
            'setup.incomplete' => EnsureProfileIsSetup::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->append(RestrictToNorthAmerica::class);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (app()->isProduction()) {
            $exceptions->reportable(function (Throwable $e) {
                ErrorLog::create([
                    'user_id' => auth()->id(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'input' => request()->except(['password', 'password_confirmation']),
                    'stack_trace' => collect($e->getTrace())->take(10),
                    'ip' => request()->ip(),
                ]);
            });
        }
    })->create();
