<?php

namespace App\Application\PlaceLifecycleEventTransport;

enum PlaceLifecycleEventRoutingStatus: string
{
    case Routed = 'routed';
    case Deferred = 'deferred';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
