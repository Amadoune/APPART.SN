<?php

namespace App\Application\AdministrativeActionLifecycleEventRouting;

enum AdministrativeActionLifecycleInboxStoreStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
