<?php

return [
    'rate_limit_per_minute' => (int) env('AUTHORING_HTTP_RATE_LIMIT_PER_MINUTE', 120),
    'risk_hmac_key' => (string) env('AUTHORING_HTTP_RISK_HMAC_KEY', 'property-listing-authoring-local-only'),
];
