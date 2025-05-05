<?php

namespace App\Repositories;

use App\Models\Gateway;

class GatewayRepository implements GatewayRepositoryInterface
{
    public function create(array $data): Gateway
    {
        return Gateway::create($data);
    }

    public function findByActiveId(string $code): ?Gateway
    {
        return Gateway::where(['id' => $code, 'is_active' => true])->first();
    }

    public function getByPriority(?string $preferredGateway = null): ?Gateway
    {
        if ($preferredGateway) {
            return Gateway::where(['code' => $preferredGateway, 'is_active' => true])
                ->orderBy('priority')
                ->first();
        }

        return Gateway::where('is_active', true)->orderBy('priority')->first();
    }
}
