<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class VerifyWebhook
{
    public function handle($request, Closure $next)
    {
        $validToken = env('WEBHOOK_TOKEN');
        $receivedToken = $request->header('asaas-access-token') ?? $request->input('authToken');

        Log::channel('webhooks')->debug('Novo - Payload recebido (antes da validação)', [
            'headers' => $request->headers->all(),
            'payload_completo' => $request->getContent(),
            'ip' => $request->ip()
        ]);

        if (!hash_equals((string)$validToken, (string)$receivedToken)) {
            Log::warning('Tentativa de webhook não autenticada', [
                'ip' => $request->ip(),
                'token_recebido' => $receivedToken
            ]);
            abort(403, 'Acesso não autorizado');
        }

        return $next($request);
    }
}
