<?php

namespace Tests\Feature;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanCreatePixPayment()
    {
        Event::fake();

        $response = $this->post('/payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'description' => 'Test payment'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.method', PaymentMethodEnum::PIX->value)
            ->assertJsonPath('data.status', PaymentStatusEnum::PROCESSING->value);

        $this->assertDatabaseHas('payments', [
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'status' => PaymentStatusEnum::PROCESSING->value
        ]);
    }

    public function testUserCanCreateCreditCardPayment()
    {
        $paymentData = [
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
        ];

        $response = $this->post('/payments', $paymentData);

        $response->assertStatus(201)
            ->assertJsonPath('data.method', PaymentMethodEnum::CREDIT_CARD->value)
            ->assertJsonPath('data.status', PaymentStatusEnum::PROCESSING->value);

        $this->assertDatabaseHas('payments', [
            'amount' => 150.75,
            'method' => PaymentMethodEnum::CREDIT_CARD->value,
            'status' => PaymentStatusEnum::PROCESSING->value
        ]);
    }

    public function testValidationFailsForInvalidCreditCardData()
    {
        $response = $this->postJson('/payments', [
            'amount' => 150.75,
            'method' => PaymentMethodEnum::CREDIT_CARD->value,
            'card' => [
                'number' => 'invalid',
                'holder_name' => '',
                'expiry_month' => '13',
                'expiry_year' => '2020',
                'cvv' => '1'
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'card.number',
                'card.holder_name',
                'card.expiry_month',
                'card.expiry_year',
                'card.cvv',
                'customer.name',
                'customer.email',
                'customer.cpf_cnpj',
                'customer.postal_code',
                'customer.address_number',
                'customer.phone'
            ]);
    }

    public function testUserCanViewPaymentDetails()
    {
        $payment = Payment::factory()->create();

        $response = $this->get("/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonPath('data.amount', $payment->amount);
    }

    public function testUserCanListPayments()
    {
        Payment::factory()->count(15)->create();

        $response = $this->get('/payments');

        $response->assertStatus(200)
            ->assertJsonCount(15, 'data.data')
            ->assertJsonStructure([
                'data' => [
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
                ]
            ]);
    }
}
