<?php

namespace Appart\Modules\RealEstateCatalog\Application\UseCase;

use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\UnavailableGeographicPlace;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

final readonly class ChangeAddress extends PropertyUseCase
{
    public function __construct(PropertyRegistry $properties, private GeographicPlaceCatalog $places)
    {
        parent::__construct($properties);
    }

    public function execute(PropertyId $id, Address $address, DateTimeImmutable $at): Property
    {
        $status = $this->places->statusOf($address->placeId);
        if ($status !== GeographicPlaceStatus::Usable) {
            throw new UnavailableGeographicPlace($status);
        }

        $property = $this->property($id);
        $version = $property->version();
        $property->changeAddress($address, $at);

        return $this->save($property, $version);
    }
}
