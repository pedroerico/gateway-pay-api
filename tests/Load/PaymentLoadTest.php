<?php

namespace Tests\Load;

use App\Enums\PaymentMethodEnum;
use App\Jobs\ProcessPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentLoadTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleMultipleSimultaneousPayments()
    {
        Queue::fake();

        $concurrentRequests = 100;
        $responses = [];

        for ($i = 0; $i < $concurrentRequests; $i++) {
            $responses[] = $this->postJson('/api/payments', [
                'amount' => 10.00 + ($i * 0.01),
                'method' => PaymentMethodEnum::PIX->value
            ]);
        }

        $successCount = 0;
        foreach ($responses as $response) {
            if ($response->getStatusCode() === 201) {
                $successCount++;
            }
        }

        $this->assertEquals($concurrentRequests, $successCount);
        $this->assertDatabaseCount('payments', $concurrentRequests);
        Queue::assertPushed(ProcessPayment::class, $concurrentRequests);
    }

    public function testPerformanceUnderLoad()
    {
        $startTime = microtime(true);
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $this->postJson('/api/payments', [
                'amount' => 10.00,
                'method' => 'pix'
            ])->assertStatus(201);
        }

        $totalTime = microtime(true) - $startTime;
        $avgTime = $totalTime / $iterations;

        $this->assertLessThan(0.5, $avgTime,
            "Average response time should be less than 500ms, actual: {$avgTime}s");
    }
}
