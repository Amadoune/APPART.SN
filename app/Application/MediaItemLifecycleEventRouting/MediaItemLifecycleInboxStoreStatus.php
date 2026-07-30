<?php

namespace App\Application\MediaItemLifecycleEventRouting;

enum MediaItemLifecycleInboxStoreStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
