<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $version = $request->header('x-api-version', '1');

        if (!in_array($version, config('api.versions'))) {
            return response()->json([
                'error' => 'Unsupported API version',
            ], 400);
        }

        $request->attributes->set('api_version', (int) $version);

        return $next($request);
    }
}
