<?php

declare(strict_types=1);

namespace App\DTO;

interface AbstractInterfaceDTO
{
    public function toArray(bool|null $snakeCase): array;

    public function jsonSerialize(): array;
}
