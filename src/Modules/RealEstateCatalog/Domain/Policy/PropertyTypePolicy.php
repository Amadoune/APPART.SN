<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Policy;

use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyViolation;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;

final readonly class PropertyTypePolicy
{
    public function assertValid(PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $year, ?Address $address, BusinessYear $businessYear): void
    {
        if ($year !== null && $year->value > $businessYear->value) {
            throw PropertyViolation::futureConstructionYear();
        }

        match ($type) {
            PropertyType::Apartment, PropertyType::House, PropertyType::Villa => $this->assertResidential($surface, $rooms, $bathrooms, $address),
            PropertyType::Land => $this->assertLand($surface, $rooms, $bathrooms, $year, $address),
            PropertyType::Office => $this->assertOffice($surface, $rooms, $bathrooms, $address),
            PropertyType::Commercial => $this->assertCommercial($surface, $rooms, $address),
            PropertyType::Other => $this->assertBathroomsDoNotExceedRooms($rooms, $bathrooms),
        };
    }

    private function assertResidential(?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?Address $address): void
    {
        $this->requireSurfaceAndAddress($surface, $address);
        $this->requireRooms($rooms);
        $this->assertBathroomsDoNotExceedRooms($rooms, $bathrooms);
    }

    private function assertLand(?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $year, ?Address $address): void
    {
        $this->requireSurfaceAndAddress($surface, $address);
        if ($rooms->value !== 0 || $bathrooms->value !== 0 || $year !== null) {
            throw PropertyViolation::invalidLandDetails();
        }
    }

    private function assertOffice(?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?Address $address): void
    {
        $this->requireSurfaceAndAddress($surface, $address);
        $this->requireRooms($rooms);
        $this->assertBathroomsDoNotExceedRooms($rooms, $bathrooms);
    }

    private function assertCommercial(?SurfaceArea $surface, RoomCount $rooms, ?Address $address): void
    {
        $this->requireSurfaceAndAddress($surface, $address);
        $this->requireRooms($rooms);
    }

    private function requireSurfaceAndAddress(?SurfaceArea $surface, ?Address $address): void
    {
        if ($surface === null) {
            throw PropertyViolation::surfaceRequired();
        }
        if ($address === null) {
            throw PropertyViolation::addressRequired();
        }
    }

    private function requireRooms(RoomCount $rooms): void
    {
        if ($rooms->value < 1) {
            throw PropertyViolation::roomsRequired();
        }
    }

    private function assertBathroomsDoNotExceedRooms(RoomCount $rooms, BathroomCount $bathrooms): void
    {
        if ($bathrooms->value > $rooms->value) {
            throw PropertyViolation::inconsistentRooms();
        }
    }
}
