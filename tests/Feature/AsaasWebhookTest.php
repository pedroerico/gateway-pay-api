<?php

namespace Tests\Feature;

use App\Enums\PaymentGatewayStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessAsaasWebhook;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AsaasWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function testWebhookUpdatesPaymentStatusToPaid()
    {
        Queue::fake();

        $payment = Payment::factory()->create([
            'gateway' => 'asaas',
            'gateway_id' => 'pay_123',
            'status' => PaymentStatusEnum::PENDING->value
        ]);

        $webhookData = [
            'event' => PaymentGatewayStatusEnum::RECEIVED->value,
            'payment' => [
                'id' => 'pay_123',
                'status' => 'RECEIVED',
                'value' => $payment->amount,
                'paidDate' => now()->toIso8601String()
            ]
        ];

        $response = $this->postJson('/webhooks/asaas', $webhookData);

        $response->assertStatus(200)
            ->assertJson(['status' => 'webhook processed']);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatusEnum::PAID->value
        ]);

        Queue::assertPushed(ProcessAsaasWebhook::class);
    }

    public function testWebhookDeletesPaymentWhenEventIsPaymentDeleted()
    {
        $payment = Payment::factory()->create([
            'gateway' => 'asaas',
            'gateway_id' => 'pay_123'
        ]);

        $webhookData = [
            'event' => PaymentGatewayStatusEnum::PAYMENT_DELETED->value,
            'payment' => [
                'id' => 'pay_123'
            ]
        ];

        $response = $this->postJson('/webhooks/asaas', $webhookData);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('payments', [
            'id' => $payment->id
        ]);
    }

    public function testWebhookReturnsOkWhenPaymentNotFound()
    {
        $webhookData = [
            'event' => PaymentGatewayStatusEnum::RECEIVED->value,
            'payment' => [
                'id' => 'non_existent_id',
                'status' => 'RECEIVED'
            ]
        ];

        $response = $this->postJson('/webhooks/asaas', $webhookData);

        $response->assertStatus(200);
    }
}
