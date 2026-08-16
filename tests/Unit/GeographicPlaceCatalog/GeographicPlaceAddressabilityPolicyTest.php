<?php

namespace Tests\Unit\GeographicPlaceCatalog;

use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceAddressability;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeographicPlaceAddressabilityPolicyTest extends TestCase
{
    #[DataProvider('matrix')]
    public function test_policy_is_exhaustive(PlaceType $type, GeographicPlaceAddressability $expected): void
    {
        self::assertSame($expected, (new GeographicPlaceAddressabilityPolicy)->evaluate($type));
    }

    /** @return iterable<string, array{PlaceType, GeographicPlaceAddressability}> */
    public static function matrix(): iterable
    {
        yield 'country' => [PlaceType::Country, GeographicPlaceAddressability::NotAddressable];
        yield 'region' => [PlaceType::Region, GeographicPlaceAddressability::NotAddressable];
        yield 'department' => [PlaceType::Department, GeographicPlaceAddressability::NotAddressable];
        yield 'city' => [PlaceType::City, GeographicPlaceAddressability::Addressable];
        yield 'district' => [PlaceType::District, GeographicPlaceAddressability::Addressable];
        yield 'neighborhood' => [PlaceType::Neighborhood, GeographicPlaceAddressability::Addressable];
    }
}
