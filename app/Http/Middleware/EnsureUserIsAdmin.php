<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Security: Only log in debug mode, never log sensitive user data
        if (config('app.debug') && config('logging.channels.single.level') === 'debug') {
            \Log::debug('EnsureUserIsAdmin middleware check', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        if (!$request->user() || !$request->user()->isAdmin()) {
            // Security: Log access denial without sensitive user info
            if (config('app.debug')) {
                \Log::warning('Admin access denied', [
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                ]);
            }
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Admin access required.',
                ],
            ], 403);
        }

        return $next($request);
    }
}
