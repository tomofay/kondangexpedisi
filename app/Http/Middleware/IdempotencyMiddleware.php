<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to POST/PUT/PATCH
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH')) {
            return $next($request);
        }

        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $user = $request->user();
        $cacheKey = 'idempotency:' . ($user ? $user->id : 'guest') . ':' . $idempotencyKey;

        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            return response()->json($cachedResponse['content'], $cachedResponse['status']);
        }

        $response = $next($request);

        // Cache successful or validation error responses (2xx or 422)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 || $response->getStatusCode() === 422) {
            Cache::put($cacheKey, [
                'content' => json_decode($response->getContent(), true),
                'status' => $response->getStatusCode(),
            ], now()->addHours(24));
        }

        return $response;
    }
}
