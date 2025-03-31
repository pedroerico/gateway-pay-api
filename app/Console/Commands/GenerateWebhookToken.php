<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWebhookToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-webhook-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerar token de segurança para webhook';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = bin2hex(random_bytes(16));
        $this->info("Token gerado: " . $token);
        $this->info("Adicione ao seu .env:");
        $this->line("WEBHOOK_TOKEN={$token}");
    }
}
