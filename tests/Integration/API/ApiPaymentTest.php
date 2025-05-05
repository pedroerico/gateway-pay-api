<?php

namespace Tests\Integration\API;

use App\Enums\PaymentMethodEnum;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatePixPayment()
    {
        Queue::fake();

        $response = $this->postJson('/api/payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'amount',
                    'method',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'status' => 'processing'
        ]);

        Queue::assertPushed(ProcessPayment::class);
    }

    public function testCreateCreditCardPayment()
    {
        Queue::fake();

        $response = $this->postJson('/api/payments', [
            'amount' => 150.75,
            'method' => PaymentMethodEnum::CREDIT_CARD->value,
            'card' => [
                'number' => '4111111111111111',
                'holder_name' => 'John Doe',
                'expiry_month' => '12',
                'expiry_year' => '2025',
                'cvv' => '123'
            ],
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf_cnpj' => '12345678901',
                'postal_code' => '12345678',
                'address_number' => '123',
                'phone' => '11999999999'
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'amount',
                    'method',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 150.75,
            'method' => PaymentMethodEnum::CREDIT_CARD->value,
            'status' => 'processing'
        ]);
    }

    public function testListPayments()
    {
        Payment::factory()->count(5)->create();

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'method',
                        'status',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page',
                    'has_more_pages'
                ]
            ]);
    }

    public function testGetPaymentDetails()
    {
        $payment = Payment::factory()->create();

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'amount',
                    'method',
                    'status',
                    'created_at'
                ]
            ]);
    }
}
