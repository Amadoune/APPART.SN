<?php

namespace App\Application\PublicGeographyRevision\Contract;

use App\Application\PublicGeographyRevision\PublicGeographyRevision;

interface PublicGeographyRevisionReader
{
    public function stableRevisionForPlace(string $placeId): ?PublicGeographyRevision;
}
