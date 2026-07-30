<?php

namespace App\Application\PropertyLifecycleEventRouting;

enum PropertyLifecycleEventDestinationStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
