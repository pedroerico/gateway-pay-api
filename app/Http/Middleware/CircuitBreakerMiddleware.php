<?php

namespace App\Http\Middleware;

use App\Services\Gateways\CircuitBreaker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CircuitBreakerMiddleware
{
    public function handle(Request $request, Closure $next, string $service): Response
    {
        if (!app(CircuitBreaker::class)->isAvailable($service)) {
            abort(503, 'Service temporarily unavailable');
        }

        return $next($request);
    }
}
