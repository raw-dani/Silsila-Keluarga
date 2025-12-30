<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitRequests
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Check if the request exceeds the rate limit
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            // Log potential DDoS attempt
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'attempts' => $this->limiter->attempts($key),
                'max_attempts' => $maxAttempts
            ]);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429, [
                'Retry-After' => $this->limiter->availableIn($key),
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($this->limiter->availableIn($key))->timestamp
            ]);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers to successful responses
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $maxAttempts - $this->limiter->attempts($key));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds($this->limiter->availableIn($key))->timestamp);

        return $response;
    }

    protected function resolveRequestSignature(Request $request)
    {
        // Use IP address and route for rate limiting
        // For authenticated users, we could also include user ID
        $key = $request->ip();

        // Add route fingerprint for more granular control
        $route = $request->route();
        if ($route) {
            $key .= '|' . $route->getDomain() . '|' . $request->method() . '|' . $route->uri();
        }

        return sha1($key);
    }
}
