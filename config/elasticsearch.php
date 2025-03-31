<?php

return [
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'elasticsearch:9200'),
    ],
    'auth' => [
        'username' => env('ELASTICSEARCH_USERNAME'),
        'password' => env('ELASTICSEARCH_PASSWORD'),
    ],
    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'laravel_logs_'),
    'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),
];
