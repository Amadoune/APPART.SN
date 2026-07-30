<?php

namespace Appart\Modules\Geography\Domain\Model;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;

final readonly class AdministrativeDivision
{
    public function __construct(
        public PlaceId $placeId,
        public PlaceType $placeType,
    ) {}
}
