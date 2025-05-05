<?php

namespace App\Repositories;

use App\Models\ApiClient;
use Illuminate\Support\Str;

class ApiClientRepository implements ApiClientRepositoryInterface
{
    public function create(array $data): ApiClient
    {
        return ApiClient::create($data);
    }

    public function findByApiKey(string $apiKey): ?ApiClient
    {
        return ApiClient::where('api_key', $apiKey)->first();
    }
}
