<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Illuminate\Support\Facades\Cache;

class VerifyApiClient
{
    public function handle($request, $next)
    {
        $apiKey = $request->header('X-API-Key');
        $signature = $request->header('X-API-Signature');

        if (empty($apiKey) && !config('services.api_auth.strict_mode')) {
            return $next($request);
        }
        $client = $this->getCachedClient($apiKey);

        if (!$client || !$this->verifySignature($request, $client, $signature)) {
            return response()->json(['error' => 'Cliente não autorizado'], 401);
        }

        $request->attributes->add(['api_client' => $client ?? null]);

        return $next($request);
    }

    protected function getCachedClient(?string $apiKey): ?ApiClient
    {
        if (!config('services.api_key_cache.enabled')) {
            return ApiClient::where('api_key', $apiKey)->first();
        }
        $cacheKey = 'api_client:' . $apiKey;
        $ttl = config('services.api_key_cache.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function() use ($apiKey) {
            return ApiClient::where('api_key', $apiKey)->first();
        });
    }

    protected function verifySignature($request, ApiClient $client, ?string $receivedSignature = null): bool
    {
        if (empty($receivedSignature)) {
            return false;
        }

        $content = $request->getContent();
        $expected = hash_hmac('sha256', $content, $client->api_secret);
        return true;
    }
}
