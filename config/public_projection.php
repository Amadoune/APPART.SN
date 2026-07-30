<?php

return [
    'delivery' => [
        'consumer_id' => env('PUBLIC_PROJECTION_CONSUMER_ID', 'public-projection'),
        'worker_id' => env('PUBLIC_PROJECTION_WORKER_ID', 'worker:public-projection'),
        'batch_size' => (int) env('PUBLIC_PROJECTION_WORKER_BATCH_SIZE', 100),
        'lease_seconds' => (int) env('PUBLIC_PROJECTION_WORKER_LEASE_SECONDS', 60),
        'maximum_attempts' => (int) env('PUBLIC_PROJECTION_RETRY_MAXIMUM_ATTEMPTS', 3),
        'retry_delay_seconds' => (int) env('PUBLIC_PROJECTION_RETRY_DELAY_SECONDS', 1),
    ],
];
