<?php

namespace App\Listeners;

use App\Events\LogEvent;
use Illuminate\Support\Facades\Log;

class LogEventListener
{
    public function handle(LogEvent $event)
    {
        $exception = $event->exception;
        $message = $event->customMessage ?: '[ERRO] ' . $exception->getMessage();

        Log::error($message, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'context' => $event->context,
        ]);
    }
}
