<?php

namespace App\Application\ListingPublicationEventRouting;

final readonly class ListingPublicationEventDestinationResult
{
    public function __construct(public ListingPublicationEventDestinationStatus $status) {}
}
