<?php

namespace Services\Gateways;

use App\Services\Gateways\CircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CircuitBreakerTest extends TestCase
{
    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->circuitBreaker = new CircuitBreaker('test_gateway', 3, 60);
        Cache::forget("circuit_breaker:test_gateway:failures");
    }

    public function testCircuitIsInitiallyAvailable()
    {
        $this->assertTrue($this->circuitBreaker->isAvailable());
    }

    public function testCircuitStaysAvailableBelowThreshold()
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();

        $this->assertTrue($this->circuitBreaker->isAvailable());
    }

    public function testCircuitTripsAtThreshold()
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();

        $this->assertFalse($this->circuitBreaker->isAvailable());
    }

    public function testCircuitResetsAfterSuccess()
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordSuccess();

        $this->assertTrue($this->circuitBreaker->isAvailable());
        $this->assertEquals(0, Cache::get("circuit_breaker:test_gateway:failures", 0));
    }

    public function testFailuresExpireAfterTimeout()
    {
        $shortTimeoutBreaker = new CircuitBreaker('test_gateway', 3, 1);
        $shortTimeoutBreaker->recordFailure();

        sleep(2); // Wait for timeout

        $this->assertTrue($shortTimeoutBreaker->isAvailable());
    }
}
