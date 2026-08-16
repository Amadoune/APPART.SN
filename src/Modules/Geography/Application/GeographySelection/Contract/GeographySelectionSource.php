<?php

namespace Appart\Modules\Geography\Application\GeographySelection\Contract;

use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionSourceResult;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;

interface GeographySelectionSource
{
    public function select(PlaceType $type, ?PlaceId $parentId, ?string $afterNormalizationKey, ?PlaceId $afterPlaceId, int $limit): GeographySelectionSourceResult;
}
