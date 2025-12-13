<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecureErrorMessages Middleware
 *
 * Sanitizes error messages in production to prevent information disclosure
 */
class SecureErrorMessages
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only sanitize in production
        if (!config('app.debug') && $response->getStatusCode() >= 400) {
            $content = $response->getContent();

            if ($content) {
                $data = json_decode($content, true);

                if (is_array($data) && isset($data['error'])) {
                    // Don't expose internal error details in production
                    if (isset($data['error']['details'])) {
                        unset($data['error']['details']);
                    }

                    // Generic error messages for production
                    $genericMessages = [
                        'UNAUTHENTICATED' => 'Authentication required.',
                        'UNAUTHORIZED' => 'You do not have permission to perform this action.',
                        'VALIDATION_ERROR' => 'Validation failed. Please check your input.',
                        'NOT_FOUND' => 'Resource not found.',
                        'SERVER_ERROR' => 'An error occurred. Please try again later.',
                    ];

                    $errorCode = $data['error']['code'] ?? null;
                    if ($errorCode && isset($genericMessages[$errorCode])) {
                        $data['error']['message'] = $genericMessages[$errorCode];
                    } elseif (isset($data['error']['message'])) {
                        // Generic fallback
                        $data['error']['message'] = 'An error occurred. Please try again later.';
                    }

                    $response->setContent(json_encode($data));
                }
            }
        }

        return $response;
    }
}
