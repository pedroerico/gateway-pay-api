<?php

namespace App\Providers;

use App\Services\Gateways\AsaasGateway;
use App\Services\Gateways\CircuitBreaker;
use App\Services\Gateways\GatewayFactory;
use App\Services\Gateways\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, AsaasGateway::class);

        $this->app->singleton(GatewayFactory::class, function ($app) {
            return new GatewayFactory(
                env('PAYMENT_GATEWAY_DEFAULT'),
                env('PAYMENT_GATEWAY_FALLBACK'),
            );
        });

        $this->app->singleton(AsaasGateway::class, function ($app) {
            return new AsaasGateway(
                env('ASAAS_KEY'),
                env('ASAAS_URL'),
                env('ASAAS_CLIENT_ID'),
            );
        });

        $this->app->singleton(CircuitBreaker::class, function ($app) {
            return new CircuitBreaker(
                env('PAYMENT_GATEWAY_DEFAULT'),
                env('CIRCUIT_BREAKER_MAX_FAILURES'),
                env('CIRCUIT_BREAKER_TIMEOUT'),
            );
        });
    }
}
