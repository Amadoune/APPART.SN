<?php

namespace Tests\Unit\Modules\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PropertyValueObjectsTest extends TestCase
{
    public function test_reference_and_address_line_are_normalized(): void
    {
        self::assertSame('PROP-DKR-001', PropertyReference::fromString(' prop-dkr-001 ')->value);
        self::assertSame('12 avenue du Sénégal', AddressLine::fromString(' 12   avenue du Sénégal ')->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, mixed $value): void
    {
        $this->expectException(InvalidPropertyValue::class);
        match ($type) {
            'property_id' => PropertyId::fromString((string) $value), 'address_id' => AddressId::fromString((string) $value), 'reference' => PropertyReference::fromString((string) $value),
            'surface' => SurfaceArea::fromSquareMeters((int) $value), 'rooms' => RoomCount::fromInt((int) $value), 'bathrooms' => BathroomCount::fromInt((int) $value),
            'year' => ConstructionYear::fromInt((int) $value), 'place' => GeographicPlaceId::fromString((string) $value), 'line' => AddressLine::fromString((string) $value),
        };
    }

    public static function invalidValues(): array
    {
        return [['property_id', 'x'], ['address_id', 'x'], ['reference', 'x'], ['surface', 0], ['rooms', -1], ['bathrooms', -1], ['year', 1700], ['place', '?'], ['line', 'x']];
    }
}
