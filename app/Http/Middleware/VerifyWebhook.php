<?php

namespace App\Http\Middleware;

use App\Models\Gateway;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyWebhook
{
    const CACHE_TTL = 3600; // 1 hora

    public function handle($request, Closure $next, $gatewayCode)
    {
        // 1. Obter configuração do gateway com cache
        $gatewayConfig = Cache::remember(
            "gateway_webhook_config:{$gatewayCode}",
            self::CACHE_TTL,
            function() use ($gatewayCode) {
                return Gateway::where('code', $gatewayCode)
                    ->select(['webhook_header', 'webhook_token'])
                    ->firstOrFail();
            }
        );

        // 2. Obter token do header dinâmico
        $receivedToken = $request->header(
            strtolower($gatewayConfig->webhook_header)
        );

        // 3. Verificar autenticação
        if (!hash_equals((string)$gatewayConfig->webhook_token, (string)$receivedToken)) {
            abort(403, 'Token de webhook inválido');
        }

        // 4. Adicionar gateway ao request
        $request->attributes->add([
            'gateway_code' => $gatewayCode,
            'gateway_config' => $gatewayConfig
        ]);

        return $next($request);
    }
}
