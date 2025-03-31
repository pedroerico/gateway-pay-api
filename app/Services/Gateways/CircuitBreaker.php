<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    public function __construct(
        public string $gateway,
        public int $failureThreshold = 3,
        public int $timeout = 60
    ) {
    }

    public function isAvailable(): bool
    {
        $failures = Cache::get("circuit_breaker:{$this->gateway}:failures", 0);
        return $failures < $this->failureThreshold;
    }

    public function recordFailure(): void
    {
        $key = "circuit_breaker:{$this->gateway}:failures";
        $failures = Cache::get($key, 0);
        Cache::put($key, $failures + 1, $this->timeout);

        if ($failures + 1 >= $this->failureThreshold) {
            Log::warning("Circuit breaker tripped for gateway: {$this->gateway}");
        }
    }

    public function recordSuccess(): void
    {
        Cache::forget("circuit_breaker:{$this->gateway}:failures");
    }
}
