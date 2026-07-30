<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

enum AdministrativeActionLifecycleEventRoutingStatus: string
{
    case Routed = 'routed';
    case Deferred = 'deferred';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
