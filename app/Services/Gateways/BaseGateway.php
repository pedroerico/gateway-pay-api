<?php

namespace App\Services\Gateways;

use App\DTO\Gateway\GatewayConfigDTO;
use App\Exceptions\GatewayException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseGateway implements PaymentGatewayInterface
{
    protected string $status;

    public function __construct(
        protected readonly GatewayConfigDTO $configDTO
    ) {
    }

    public function getGatewayId(): string
    {
        return $this->configDTO->id;
    }

    public function getName(): string
    {
        return $this->configDTO->name;
    }

    abstract public function supportsMethod(string $method): bool;

    protected function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        ?array $customHeaders = []
    ): Response {
        $url = "{$this->configDTO->baseUrl}/{$endpoint}";

        $response = Http::withHeaders(array_merge(
            $this->getDefaultHeaders(),
            $customHeaders
        ))->$method($url, $data);

        if ($response->failed()) {
            $this->handleRequestError($response);
        }

        return $response;
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function handleRequestError(Response $response): void
    {
        $status = $response->status();
        $statusMessages = [
            401 => 'Não autorizado - API Key inválida ou ausente',
            403 => 'Acesso proibido',
            404 => 'Recurso não encontrado',
            422 => 'Dados inválidos',
            429 => 'Muitas requisições',
            500 => 'Erro interno do servidor',
        ];

        $message = $statusMessages[$status] ?? 'Erro ao processar a requisição no ' . $this->getName();
        $responseData = $response->json() ?? $response->body();

        Log::error($message, [
            'gateway' => $this->getName(),
            'url' => $response->effectiveUri(),
            'status' => $status,
            'response' => $responseData,
        ]);

        if (str_starts_with((string)$status, '4')) {
            throw new GatewayException(
                gatewayName: $this->getName(),
                message: $message,
                code: $status,
                errors: is_array($responseData) ? ($responseData['errors'] ?? []) : []
            );
        }

        throw new \Exception("{$this->getName()} Error: {$message} [Status: {$status}]");
    }

    protected function detectCardBrand(string $cardNumber): string
    {
        $firstDigit = substr($cardNumber, 0, 1);
        return match ($firstDigit) {
            '4' => 'visa',
            '5' => 'master',
            default => throw new \InvalidArgumentException('Cartão invalido'),
        };
    }
}
