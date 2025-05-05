<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

class LogEvent
{
    use Dispatchable;

    public function __construct(
        public Throwable $exception,
        public ?string $customMessage = null,
        public array $context = []
    ) {
    }
}
