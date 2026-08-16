<?php

namespace App\Application\PropertyAuthoringGeographySelection\Contract;

use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayResult;

interface GeographySelectionReplayValidatorV1
{
    public function validate(
        string $geographicPlaceId,
        string $type,
        ?string $parentPlaceId,
        ?string $cursor,
        int $limit,
    ): GeographySelectionReplayResult;
}
