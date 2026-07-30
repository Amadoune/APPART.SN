<?php

namespace App\Application\PublicProjectionSourceLookup;

final readonly class MediaCollectionPropertyResolution
{
    public function __construct(public MediaCollectionPropertyStatus $status, public ?string $propertyId = null) {}
}
