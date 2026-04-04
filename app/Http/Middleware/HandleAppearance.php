<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('HandleAppearance', [
            'user' => $request->user()?->id,
            'cookie' => $request->cookie('appearance'),
        ]);

        $appearance = $request->user()
            ? ($request->cookie('appearance') ?? 'system')
            : 'light';

        View::share('appearance', $appearance);

        return $next($request);
    }
}
