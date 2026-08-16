<?php

namespace App\Application\PublicGeographyRefresh\Contract;

use App\Application\PublicGeographyRefresh\AffectedPublicGeographyTerminalPage;

interface AffectedPublicGeographyTerminalReaderV1
{
    public function read(string $mutatedPlaceId, ?string $cursor = null, int $limit = 100): AffectedPublicGeographyTerminalPage;
}
