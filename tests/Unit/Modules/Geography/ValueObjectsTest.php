<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Domain\Exception\InvalidCoordinates;
use Appart\Modules\Geography\Domain\Exception\InvalidCountryCode;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceCode;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceId;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceName;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_place_identifiers_compare_by_value(): void
    {
        $first = PlaceId::fromString('00000000-0000-4000-8000-000000000001');
        $same = PlaceId::fromString('00000000-0000-4000-8000-000000000001');
        $other = PlaceId::fromString('00000000-0000-4000-8000-000000000002');

        self::assertTrue($first->equals($same));
        self::assertFalse($first->equals($other));
    }

    public function test_it_rejects_an_invalid_place_identifier(): void
    {
        $this->expectException(InvalidPlaceId::class);

        PlaceId::fromString('dakar');
    }

    #[DataProvider('invalidNameProvider')]
    public function test_it_rejects_an_invalid_place_name(string $name): void
    {
        $this->expectException(InvalidPlaceName::class);

        PlaceName::fromString($name);
    }

    public function test_place_names_are_trimmed_and_compared_case_insensitively(): void
    {
        $first = PlaceName::fromString('  Grand   Dakar ');
        $same = PlaceName::fromString('grand dakar');

        self::assertSame('Grand Dakar', $first->value);
        self::assertTrue($first->equals($same));
    }

    public function test_place_codes_are_canonicalized(): void
    {
        self::assertSame('DKR-01', PlaceCode::fromString(' dkr-01 ')->value);
    }

    public function test_it_rejects_an_invalid_place_code(): void
    {
        $this->expectException(InvalidPlaceCode::class);

        PlaceCode::fromString('Dakar centre');
    }

    public function test_country_codes_are_canonicalized(): void
    {
        self::assertSame('SN', CountryCode::fromString('sn')->value);
    }

    public function test_it_rejects_an_invalid_country_code(): void
    {
        $this->expectException(InvalidCountryCode::class);

        CountryCode::fromString('SEN');
    }

    #[DataProvider('invalidCoordinatesProvider')]
    public function test_it_rejects_coordinates_outside_world_bounds(float $latitude, float $longitude): void
    {
        $this->expectException(InvalidCoordinates::class);

        Coordinates::fromDecimal($latitude, $longitude);
    }

    public function test_coordinates_accept_the_inclusive_world_boundaries(): void
    {
        self::assertSame(90.0, Coordinates::fromDecimal(90.0, 180.0)->latitude);
        self::assertSame(-180.0, Coordinates::fromDecimal(-90.0, -180.0)->longitude);
    }

    public function test_coordinates_reject_non_finite_values(): void
    {
        $this->expectException(InvalidCoordinates::class);

        Coordinates::fromDecimal(INF, NAN);
    }

    /** @return array<string, array{string}> */
    public static function invalidNameProvider(): array
    {
        return [
            'empty' => [''],
            'one character' => ['D'],
            'too long' => [str_repeat('D', 121)],
        ];
    }

    /** @return array<string, array{float, float}> */
    public static function invalidCoordinatesProvider(): array
    {
        return [
            'latitude too high' => [90.0001, 0.0],
            'latitude too low' => [-90.0001, 0.0],
            'longitude too high' => [0.0, 180.0001],
            'longitude too low' => [0.0, -180.0001],
        ];
    }
}
