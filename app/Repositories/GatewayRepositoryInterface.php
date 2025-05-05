<?php

namespace App\Repositories;

use App\Models\Gateway;

interface GatewayRepositoryInterface
{
    public function create(array $data): Gateway;
    public function findByActiveId(string $code): ?Gateway;

    public function getByPriority(?string $preferredGateway = null): ?Gateway;
}
