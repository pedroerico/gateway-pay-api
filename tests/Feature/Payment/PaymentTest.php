<?php

namespace Payment;

use App\Enums\PaymentMethodEnum;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_payment_with_pix()
    {
        Queue::fake();

        $response = $this->postJson('/api/payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'client_id' => 'cus_123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'method',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'status' => 'pending'
        ]);

        Queue::assertPushed(ProcessPayment::class);
    }

    public function test_create_payment_with_credit_card()
    {
        $response = $this->postJson('/api/payments', [
            'amount' => 200.75,
            'method' => PaymentMethodEnum::CREDIT_CARD->value,
            'client_id' => 'cus_123',
            'credit_card' => [
                'holder_name' => 'John Doe',
                'number' => '4111111111111111',
                'expiry_date' => '12/25',
                'ccv' => '123',
                'cpf' => '12345678901',
                'email' => 'john@example.com',
                'phone' => '11999999999',
                'postal_code' => '12345678',
                'address_number' => '123'
            ]
        ]);

        $response->assertStatus(201);
    }

    public function test_get_payment_details()
    {
        $payment = Payment::factory()->create([
            'external_id' => 'pay_123',
            'amount' => 150.00,
            'method' => PaymentMethodEnum::PIX->value
        ]);

        $response = $this->getJson("/api/payments/pay_123");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 'pay_123',
                    'amount' => 150.00,
                    'method' => PaymentMethodEnum::PIX->value
                ]
            ]);
    }
}
