<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * RateLimitAPI Middleware
 *
 * Additional rate limiting for API endpoints beyond Laravel's default throttling
 */
class RateLimitAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api:' . $request->ip() . ':' . $request->path();

        // Allow 100 requests per minute per IP per endpoint
        $executed = RateLimiter::attempt(
            $key,
            $perMinute = 100,
            function () use ($next, $request) {
                return $next($request);
            }
        );

        if (!$executed) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Too many requests. Please try again later.',
                ],
            ], 429);
        }

        return $executed;
    }
}
