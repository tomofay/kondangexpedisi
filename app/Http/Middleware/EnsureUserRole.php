<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowed = count($roles) > 0 ? $roles : config('expedition.roles', []);

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }

        return $next($request);
    }
}
