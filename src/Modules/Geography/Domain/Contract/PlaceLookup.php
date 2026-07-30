<?php

namespace Appart\Modules\Geography\Domain\Contract;

use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

interface PlaceLookup
{
    public function find(PlaceId $id): ?Place;
}
