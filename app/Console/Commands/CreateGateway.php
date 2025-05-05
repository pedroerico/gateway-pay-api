<?php

namespace App\Console\Commands;

use App\DTO\Gateway\CreateGatewayDTO;
use App\Services\GatewayService;
use Illuminate\Console\Command;

class CreateGateway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:create
                            {name : Nome do gateway}
                            {code : Código único}
                            {base_url : URL base do gateway}
                            {api_key : Chave da API do gateway}
                            {webhook_header? : Header para verificação do webhook}
                            {--priority= : Prioridade (opcional)}
                            {--config= : Configurações em JSON (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo gateway de pagamento';

    /**
     * Execute the console command.
     */
    public function handle(GatewayService $service): void
    {
        try {
            $dto = CreateGatewayDTO::fromArray([...$this->argument(), ...$this->option()]);
            $gateway = $service->createGateway($dto);

            $this->info('✅ Gateway criado com sucesso!');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $gateway->id],
                    ['Nome', $gateway->name],
                    ['Código', $gateway->code],
                    ['Prioridade', $gateway->priority],
                    ['Webhook Token', $gateway->webhook_token]
                ]
            );
        } catch (\Exception $e) {
            $this->error('Erro ao criar gateway: ' . $e->getMessage());
        }
    }
}
