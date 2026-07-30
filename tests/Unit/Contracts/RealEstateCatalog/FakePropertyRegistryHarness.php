<?php

namespace Tests\Unit\Contracts\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Unit\Modules\RealEstateCatalog\Support\FakePropertyRegistry;

class FakePropertyRegistryHarness implements PropertyRegistryHarness
{
    public function freshRegistry(): PropertyRegistry
    {
        return new FakePropertyRegistry;
    }

    public function minimalProperty(?PropertyId $id = null, ?PropertyReference $reference = null): Property
    {
        return Property::register(
            $id ?? $this->primaryId(),
            $reference ?? $this->primaryReference(),
            PropertyType::Apartment,
            SurfaceArea::fromSquareMeters(120),
            RoomCount::fromInt(5),
            BathroomCount::fromInt(2),
            ConstructionYear::fromInt(2018),
            $this->address(1),
            BusinessYear::fromInt(2026),
            new PropertyTypePolicy,
            $this->at(0),
        );
    }

    public function propertyWithHistory(?PropertyId $id = null, ?PropertyReference $reference = null): Property
    {
        $property = $this->minimalProperty($id, $reference);
        $this->mutate($property);
        $property->changeAddress($this->address(2), $this->at(2));

        return $property;
    }

    public function archivedProperty(?PropertyId $id = null, ?PropertyReference $reference = null): Property
    {
        $property = $this->propertyWithHistory($id, $reference);
        $property->archive($this->at(3));

        return $property;
    }

    public function primaryId(): PropertyId
    {
        return PropertyId::fromString('33000000-0000-4000-8000-000000000001');
    }

    public function distinctId(): PropertyId
    {
        return PropertyId::fromString('33000000-0000-4000-8000-000000000002');
    }

    public function primaryReference(): PropertyReference
    {
        return PropertyReference::fromString('PROP-CONTRACT-001');
    }

    public function distinctReference(): PropertyReference
    {
        return PropertyReference::fromString('PROP-CONTRACT-002');
    }

    public function mutate(Property $property): void
    {
        $property->update(PropertyType::Villa, SurfaceArea::fromSquareMeters(150), RoomCount::fromInt(6), BathroomCount::fromInt(3), ConstructionYear::fromInt(2020), BusinessYear::fromInt(2026), new PropertyTypePolicy, $this->at(1));
    }

    public function failNextWrite(PropertyRegistry $registry): void
    {
        Assert::assertInstanceOf(FakePropertyRegistry::class, $registry);
        $registry->failNextSave();
    }

    private function address(int $suffix): Address
    {
        return new Address(
            AddressId::fromString(sprintf('33000000-0000-4000-8000-%012d', 100 + $suffix)),
            GeographicPlaceId::fromString($suffix === 1 ? 'place:dakar' : 'place:thies'),
            AddressLine::fromString($suffix === 1 ? '12 avenue du Senegal' : '8 avenue Lat Dior'),
        );
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('2026-07-17T11:%02d:00+00:00', $minute));
    }
}
