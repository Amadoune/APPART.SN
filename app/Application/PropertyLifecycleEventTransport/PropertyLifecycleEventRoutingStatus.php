<?php

namespace App\Application\PropertyLifecycleEventTransport;

enum PropertyLifecycleEventRoutingStatus: string
{
    case Routed = 'routed';
    case Deferred = 'deferred';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
