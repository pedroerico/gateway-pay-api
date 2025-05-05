<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\DTO\Payment\PaymentDTO;
use App\Events\LogEvent;
use App\Exceptions\GatewayException;
use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentRequest;
use App\Http\Resources\PaymentCollectionResource;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {
    }

    public function index(): Response
    {
        $payments = $this->paymentService->getPayments();
        return response(new PaymentCollectionResource($payments));
    }

    public function show(string $id): Response
    {
        try {
            $payment = $this->paymentService->getPaymentById($id);
            return response(new PaymentResource($payment), Response::HTTP_OK);
        } catch (PaymentException $e) {
            return response()->json(
                ['error' => 'Erro ao consultar pagamento : ' . $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function store(PaymentRequest $request): Response
    {
        try {
            $paymentDTO = PaymentDTO::FromRequest($request->validated());
            $payment = $this->paymentService->create($paymentDTO, $request->attributes->get('api_client'));
            return response(new PaymentResource($payment), Response::HTTP_CREATED);
        } catch (GatewayException $e) {
            return response()->json(
                ['message' => $e->getMessage(), 'errors' => $e->getErrors()],
                $e->getCode()
            );
        } catch (\Exception $e) {
            event(new LogEvent($e));
            return response()->json([
                'message' => 'Erro interno no servidor',
            ], 500);
        }

    }
}
