<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAsaasWebhook;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsaasWebhookController extends Controller
{
    public function __construct()
    {
    }

    public function handle(Request $request): response
    {
        ProcessAsaasWebhook::dispatch($request->all())->onQueue('webhooks');

        return response(['status' => 'webhook processed']);
    }
}
