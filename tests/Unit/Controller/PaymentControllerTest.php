<?php

namespace Controller;

use App\DTO\Payment\PaymentDTO;
use App\Http\Controllers\API\PaymentController;
use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    private $paymentServiceMock;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->controller = new PaymentController($this->paymentServiceMock);
    }

    public function testIndexReturnsPayments()
    {
        $payments = Payment::factory()->count(3)->make();

        $this->paymentServiceMock->shouldReceive('getPayments')
            ->once()
            ->andReturn($payments);

        $response = $this->controller->index();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowReturnsPaymentWhenFound()
    {
        $payment = Payment::factory()->make(['id' => 'test_id']);

        $this->paymentServiceMock->shouldReceive('getPaymentById')
            ->with('test_id')
            ->once()
            ->andReturn($payment);

        $response = $this->controller->show('test_id');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testShowReturnsNotFoundWhenPaymentDoesNotExist()
    {
        $this->paymentServiceMock->shouldReceive('getPaymentById')
            ->with('invalid_id')
            ->once()
            ->andThrow(new \App\Exceptions\PaymentException('Pagamento não encontrado'));

        $response = $this->controller->show('invalid_id');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testStoreCreatesNewPayment()
    {
        $paymentData = [
            'amount' => 100.50,
            'method' => 'pix',
            'description' => 'Test payment'
        ];

        $payment = Payment::factory()->make($paymentData);
        $paymentDTO = PaymentDTO::FromRequest($paymentData);

        $request = Mockery::mock(PaymentRequest::class);
        $request->shouldReceive('validated')->andReturn($paymentData);

        $this->paymentServiceMock->shouldReceive('create')
            ->with(Mockery::type(PaymentDTO::class))
            ->once()
            ->andReturn($payment);

        $response = $this->controller->store($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

