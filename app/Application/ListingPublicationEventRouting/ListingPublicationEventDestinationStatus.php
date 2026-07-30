<?php

namespace App\Application\ListingPublicationEventRouting;

enum ListingPublicationEventDestinationStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case Unavailable = 'unavailable';
    case RetryableFailure = 'retryable_failure';
    case Rejected = 'rejected';
}
