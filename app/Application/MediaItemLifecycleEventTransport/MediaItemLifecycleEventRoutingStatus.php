<?php

namespace App\Application\MediaItemLifecycleEventTransport;

enum MediaItemLifecycleEventRoutingStatus: string
{
    case Routed = 'routed';
    case Deferred = 'deferred';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
