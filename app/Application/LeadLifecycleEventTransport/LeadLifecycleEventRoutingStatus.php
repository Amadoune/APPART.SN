<?php

namespace App\Application\LeadLifecycleEventTransport;

enum LeadLifecycleEventRoutingStatus: string
{
    case Routed = 'routed';
    case Deferred = 'deferred';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
