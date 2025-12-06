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
        \Log::info('EnsureUserIsAdmin middleware', [
            'path' => $request->path(),
            'method' => $request->method(),
            'has_user' => $request->user() !== null,
            'user_role' => $request->user()?->role,
            'is_admin' => $request->user()?->isAdmin(),
        ]);

        if (!$request->user() || !$request->user()->isAdmin()) {
            \Log::warning('Admin access denied', [
                'path' => $request->path(),
                'user_id' => $request->user()?->id,
            ]);
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
