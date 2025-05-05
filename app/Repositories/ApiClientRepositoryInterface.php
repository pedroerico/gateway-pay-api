<?php

namespace App\Repositories;

use App\Models\ApiClient;

interface ApiClientRepositoryInterface
{
    public function create(array $data): ApiClient;
    public function findByApiKey(string $apiKey): ?ApiClient;
}
