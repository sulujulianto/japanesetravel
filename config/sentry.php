<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'release' => env('SENTRY_RELEASE'),

    'environment' => env('SENTRY_ENVIRONMENT'),

    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.05),

    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    'enable_logs' => false,

    'ignore_transactions' => [
        '/up',
    ],
];
