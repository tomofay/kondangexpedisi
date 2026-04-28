<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBearerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (blank($request->bearerToken())) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}
