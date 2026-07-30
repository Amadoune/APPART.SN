<?php

namespace App\Application\PlaceLifecycleEventRouting;

enum PlaceLifecycleInboxStoreStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
