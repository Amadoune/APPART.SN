<?php

namespace App\Application\LeadLifecycleEventRouting;

enum LeadLifecycleInboxStoreStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
