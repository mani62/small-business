<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TaskRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limit = '100', string $decay = '60'): Response
    {
        $key = 'task_requests:' . $request->user()?->id ?? $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, (int) $limit)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => "Too many requests. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }
        
        RateLimiter::hit($key, (int) $decay);
        
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, (int) $limit));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds((int) $decay)->timestamp);
        
        return $response;
    }
}
