<?php

return [
    'cookie' => [
        'name' => env('IAM_SESSION_COOKIE', '__Host-appart_session'),
        'path' => '/',
        'domain' => null,
        'same_site' => 'strict',
    ],
    'rate_limit' => [
        'login_per_minute' => 5,
        'recovery_per_minute' => 3,
        'authenticated_per_minute' => 60,
    ],
    'risk_hmac_key' => env('IAM_HTTP_RISK_HMAC_KEY', env('APP_KEY')),
];
