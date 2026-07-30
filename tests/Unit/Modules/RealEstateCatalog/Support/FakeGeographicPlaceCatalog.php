<?php

namespace Tests\Unit\Modules\RealEstateCatalog\Support;

use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;

final readonly class FakeGeographicPlaceCatalog implements GeographicPlaceCatalog
{
    /** @param array<string, GeographicPlaceStatus> $statuses */
    public function __construct(private array $statuses = ['place:dakar' => GeographicPlaceStatus::Usable, 'place:thies' => GeographicPlaceStatus::Usable]) {}

    public function statusOf(GeographicPlaceId $placeId): GeographicPlaceStatus
    {
        return $this->statuses[$placeId->value] ?? GeographicPlaceStatus::NotFound;
    }
}
