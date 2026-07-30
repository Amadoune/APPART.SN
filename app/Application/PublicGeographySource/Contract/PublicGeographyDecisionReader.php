<?php

namespace App\Application\PublicGeographySource\Contract;

use App\Application\PublicGeographySource\PublicGeographyReadResult;

interface PublicGeographyDecisionReader
{
    public function read(string $placeId): PublicGeographyReadResult;
}
