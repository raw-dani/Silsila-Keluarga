<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BruteForceProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 15): Response
    {
        $key = $this->throttleKey($request);

        // Check if IP is blocked
        if ($this->isBlocked($key)) {
            return response()->json([
                'message' => 'Too many failed attempts. Please try again later.',
                'retry_after' => $this->getRemainingBlockTime($key)
            ], 429);
        }

        $response = $next($request);

        // If login failed (401), increment attempts
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            $this->incrementAttempts($key, $decayMinutes);
        }

        // If login successful, clear attempts
        if ($response->getStatusCode() === 200 && $request->is('*/login')) {
            $this->clearAttempts($key);
        }

        return $response;
    }

    protected function throttleKey(Request $request)
    {
        return 'login_attempts:' . $request->ip();
    }

    protected function isBlocked($key)
    {
        return Cache::has($key . ':blocked');
    }

    protected function getRemainingBlockTime($key)
    {
        return Cache::get($key . ':blocked', 0);
    }

    protected function incrementAttempts($key, $decayMinutes)
    {
        $attempts = Cache::get($key, 0) + 1;

        if ($attempts >= 5) { // Block after 5 attempts
            Cache::put($key . ':blocked', now()->addMinutes(15)->timestamp, 15);
            Cache::forget($key); // Clear attempt counter
        } else {
            Cache::put($key, $attempts, $decayMinutes);
        }
    }

    protected function clearAttempts($key)
    {
        Cache::forget($key);
        Cache::forget($key . ':blocked');
    }
}
