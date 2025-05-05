<?php

namespace App\Console\Commands;

use App\DTO\ApiClient\CreateApiClientDTO;
use App\Services\ApiClientService;
use Illuminate\Console\Command;

class CreateApiClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-client:create
                            {name : Nome do cliente API}
                            {--ip=* : IPs permitidos (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera um novo cliente API para integração com o microserviço';

    /**
     * Execute the console command.
     */
    public function handle(ApiClientService $service): void
    {
        try {
            $dto = CreateApiClientDTO::fromArray([...$this->argument(), ...$this->option()]);

            $apiClient = $service->create($dto);

            $this->info('✅ Cliente API criado com sucesso!');
            $this->warn('⚠️ Guarde estas credenciais com segurança:');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $apiClient->id],
                    ['Nome', $apiClient->name],
                    ['API Key', $apiClient->api_key],
                    ['API Secret', $apiClient->api_secret],
                    ['IPs Permitidos', implode(', ', $apiClient->allowed_ips ?? [])]
                ]
            );
        } catch (\Exception $e) {
            $this->error('Erro ao criar cliente API : ' . $e->getMessage());
        }
    }
}
