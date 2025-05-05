<?php

namespace App\Providers;

use App\Models\Gateway;
use App\Observers\GatewayObserver;
use App\Repositories\ApiClientCustomerRepository;
use App\Repositories\ApiClientCustomerRepositoryInterface;
use App\Repositories\ApiClientRepository;
use App\Repositories\ApiClientRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\GatewayCustomerRepository;
use App\Repositories\GatewayCustomerRepositoryInterface;
use App\Repositories\GatewayRepository;
use App\Repositories\GatewayRepositoryInterface;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(GatewayRepositoryInterface::class, GatewayRepository::class);
        $this->app->bind(ApiClientRepositoryInterface::class, ApiClientRepository::class);
        $this->app->bind(ApiClientCustomerRepositoryInterface::class, ApiClientCustomerRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(GatewayCustomerRepositoryInterface::class, GatewayCustomerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gateway::observe(GatewayObserver::class);
    }
}
