<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'pix_data' => $this->getArray($this->pix_data),
            'boleto_data' => $this->getArray($this->boleto_data),
            'credit_card_data' => $this->getArray($this->credit_card_data),
            'created_at' => $this->created_at,
            'paid_at' => $this->paid_at,
        ];
    }

    private function getArray(string|array|null $data): ?array
    {
        if (is_array($data)) {
            return $data;
        }
        if ($data === null) {
            return null;
        }

        $decoded = json_decode($data, true);

        return is_array($decoded) ? $decoded : null;
    }
}
