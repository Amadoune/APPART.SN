<?php

namespace App\Application\PublicGeographySource;

use InvalidArgumentException;

final readonly class PublicGeographyRevisionVectorItemV2
{
    public function __construct(public string $placeId, public int $aggregateVersion)
    {
        if (trim($placeId) === '' || $aggregateVersion < 1) {
            throw new InvalidArgumentException('Invalid public Geography V2 revision vector item.');
        }
    }
}
