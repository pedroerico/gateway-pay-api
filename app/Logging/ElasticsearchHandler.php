<?php

namespace App\Logging;


use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class ElasticsearchHandler extends AbstractProcessingHandler
{
    private Client $client;
    private string $index;
    private bool $silentFailures;

    public function __construct(
        $level = Level::Debug,
        bool $bubble = true,
        bool $silentFailures = true
    ) {
        parent::__construct($level, $bubble);

        $this->silentFailures = $silentFailures;
        $this->client = $this->buildClient();
        $this->index = config('elasticsearch.index_prefix', 'laravel_logs_') . date('Y_m_d');
    }

    protected function buildClient(): Client
    {
        return ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->setRetries(2)
            ->build();
    }

    protected function write(LogRecord $record): void
    {
        try {
            $this->client->index([
                'index' => $this->index,
                'body' => $this->formatRecord($record),
            ]);
        } catch (\Throwable $e) {
            if (!$this->silentFailures) {
                throw $e;
            }

            error_log(sprintf(
                "Elasticsearch log failed: %s\nMessage: %s\nContext: %s",
                $e->getMessage(),
                $record->message,
                json_encode($record->context)
            ));
        }
    }

    protected function formatRecord(LogRecord $record): array
    {
        return [
            '@timestamp' => $record->datetime->format('c'),
            'level' => $record->level->name,
            'message' => $record->message,
            'context' => $record->context,
            'channel' => $record->channel,
            'extra' => $record->extra,
        ];
    }
}
