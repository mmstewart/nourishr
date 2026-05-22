<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToNorthAmerica
{
    protected array $allowed = ['US', 'CA'];

    public function handle(Request $request, Closure $next): Response
    {
        $country = $request->header('x-vercel-ip-country');

        if (!in_array($country, $this->allowed)) {
            abort(403, 'This service is not available in your region.');
        }

        return $next($request);
    }
}
