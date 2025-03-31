<?php

declare(strict_types=1);

namespace App\DTO;

use Illuminate\Support\Str;

abstract class AbstractDTO implements AbstractInterfaceDTO
{
    public function toArray(bool|null $snakeCase = true): array
    {
        return collect(get_object_vars($this))
            ->mapWithKeys(function ($value, $key) use ($snakeCase) {
                $transformedKey = $snakeCase ? Str::snake($key) : $key;

                $transformedValue = $this->transformValue($value, $snakeCase);

                return [$transformedKey => $transformedValue];
            })
            ->all();
    }

    protected function transformValue(mixed $value, bool $snakeCase): mixed
    {
        return match (true) {
            $value instanceof AbstractDTO => $value->toArray($snakeCase),
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_array($value) => collect($value)->map(
                fn($item) => $this->transformValue($item, $snakeCase)
            )->all(),
            default => $value
        };
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
