<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Model;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;

final readonly class Address
{
    public function __construct(public AddressId $id, public GeographicPlaceId $placeId, public AddressLine $line) {}

    public function equals(self $other): bool
    {
        return $this->placeId->equals($other->placeId) && $this->line->equals($other->line);
    }
}
