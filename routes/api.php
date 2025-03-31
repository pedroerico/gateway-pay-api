<?php

use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\Webhooks\AsaasWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    Route::post('/webhooks/asaas', [AsaasWebhookController::class, 'handle'])
        ->middleware('verify.webhook');
});
