<?php

declare(strict_types=1);

namespace App\Services\ResponseCache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\ResponseCache\CacheProfiles\BaseCacheProfile;
use Symfony\Component\HttpFoundation\Response;

class CustomCacheProfile extends BaseCacheProfile
{
    /**
     * Incorporate the custom x-api-version header into the cache key
     * to ensure api versions are cached distinctly from each other
     */
    public function useCacheNameSuffix(Request $request): string
    {
        $apiVersion = $request->header('x-api-version', '1');

        return Auth::check()
            ? Auth::id().'|v'.$apiVersion
            : '|v'.$apiVersion;
    }

    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->ajax()) {
            return false;
        }

        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }

    public function shouldCacheResponse(Response $response): bool
    {
        if (!$this->hasCacheableResponseCode($response)) {
            return false;
        }

        return $this->hasCacheableContentType($response);
    }

    public function hasCacheableResponseCode(Response $response): bool
    {
        if ($response->isSuccessful()) {
            return true;
        }

        return $response->isRedirection();
    }

    public function hasCacheableContentType(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        if (str_starts_with((string) $contentType, 'text/')) {
            return true;
        }

        if ($contentType === null || $contentType === '' || $contentType === '0') {
            return false;
        }

        return Str::contains($contentType, ['/json', '+json']);
    }
}
