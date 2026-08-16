<?php

namespace Tests\Unit\GeographicPlaceCatalog;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GeographyBackedGeographicPlaceCatalogTest extends TestCase
{
    #[DataProvider('enabledMatrix')]
    public function test_enabled_place_mapping(PlaceType $type, GeographicPlaceStatus $expected): void
    {
        $place = $this->place($type);
        self::assertSame($expected, $this->catalog(new CatalogPlaceRegistry($place))->statusOf($this->catalogId($place)));
    }

    /** @return iterable<string, array{PlaceType, GeographicPlaceStatus}> */
    public static function enabledMatrix(): iterable
    {
        yield 'country' => [PlaceType::Country, GeographicPlaceStatus::NotAddressable];
        yield 'region' => [PlaceType::Region, GeographicPlaceStatus::NotAddressable];
        yield 'department' => [PlaceType::Department, GeographicPlaceStatus::NotAddressable];
        yield 'city' => [PlaceType::City, GeographicPlaceStatus::Usable];
        yield 'district' => [PlaceType::District, GeographicPlaceStatus::Usable];
        yield 'neighborhood' => [PlaceType::Neighborhood, GeographicPlaceStatus::Usable];
    }

    public function test_missing_is_not_found(): void
    {
        self::assertSame(GeographicPlaceStatus::NotFound, $this->catalog(new CatalogPlaceRegistry)->statusOf($this->id('10000000-0000-4000-8000-000000000099')));
    }

    public function test_disabled_is_disabled(): void
    {
        $place = $this->place(PlaceType::City);
        $place->disable($this->at('+1 second'));
        self::assertSame(GeographicPlaceStatus::Disabled, $this->catalog(new CatalogPlaceRegistry($place))->statusOf($this->catalogId($place)));
    }

    public function test_merged_precedes_disabled_without_following_target(): void
    {
        $source = $this->place(PlaceType::City);
        $target = $this->place(PlaceType::City, '10000000-0000-4000-8000-000000000091', 'TARGET');
        $source->mergeInto($target, $this->at('+1 second'));
        $registry = new CatalogPlaceRegistry($source);

        self::assertSame(GeographicPlaceStatus::Merged, $this->catalog($registry)->statusOf($this->catalogId($source)));
        self::assertSame(1, $registry->findCalls);
    }

    public function test_dependency_and_corruption_fail_closed(): void
    {
        foreach ([new RuntimeException('dependency unavailable'), new RuntimeException('corrupt snapshot')] as $failure) {
            try {
                $this->catalog(new CatalogPlaceRegistry(failure: $failure))->statusOf($this->id('10000000-0000-4000-8000-000000000099'));
                self::fail('The source failure must propagate.');
            } catch (RuntimeException $caught) {
                self::assertSame($failure, $caught);
            }
        }
    }

    private function catalog(PlaceRegistry $registry): GeographyBackedGeographicPlaceCatalog
    {
        return new GeographyBackedGeographicPlaceCatalog($registry, new GeographicPlaceAddressabilityPolicy);
    }

    private function place(PlaceType $type, string $id = '10000000-0000-4000-8000-000000000001', string $code = 'PLACE'): Place
    {
        $country = Place::create(PlaceId::fromString('10000000-0000-4000-8000-000000000010'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $this->at());
        if ($type === PlaceType::Country) {
            return Place::create(PlaceId::fromString($id), PlaceName::fromString('Place'), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at());
        }
        $region = Place::create(PlaceId::fromString('10000000-0000-4000-8000-000000000011'), PlaceName::fromString('Region'), PlaceCode::fromString('REG'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $country);
        if ($type === PlaceType::Region) {
            return Place::create(PlaceId::fromString($id), PlaceName::fromString('Place'), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at(), $country);
        }
        $department = Place::create(PlaceId::fromString('10000000-0000-4000-8000-000000000012'), PlaceName::fromString('Department'), PlaceCode::fromString('DEP'), PlaceType::Department, CountryCode::fromString('SN'), $this->at(), $region);
        if ($type === PlaceType::Department) {
            return Place::create(PlaceId::fromString($id), PlaceName::fromString('Place'), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at(), $region);
        }
        $city = Place::create(PlaceId::fromString('10000000-0000-4000-8000-000000000013'), PlaceName::fromString('City'), PlaceCode::fromString('CITY'), PlaceType::City, CountryCode::fromString('SN'), $this->at(), $department);
        if ($type === PlaceType::City) {
            return Place::create(PlaceId::fromString($id), PlaceName::fromString('Place'), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at(), $department);
        }
        $district = Place::create(PlaceId::fromString('10000000-0000-4000-8000-000000000014'), PlaceName::fromString('District'), PlaceCode::fromString('DIST'), PlaceType::District, CountryCode::fromString('SN'), $this->at(), $city);

        return Place::create(PlaceId::fromString($id), PlaceName::fromString('Place'), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at(), $type === PlaceType::District ? $city : $district);
    }

    private function catalogId(Place $place): GeographicPlaceId
    {
        return $this->id($place->id()->value);
    }

    private function id(string $id): GeographicPlaceId
    {
        return GeographicPlaceId::fromString($id);
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-14T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}

final class CatalogPlaceRegistry implements PlaceRegistry
{
    public int $findCalls = 0;

    public function __construct(private ?Place $place = null, private ?RuntimeException $failure = null) {}

    public function find(PlaceId $id): ?Place
    {
        $this->findCalls++;
        if ($this->failure !== null) {
            throw $this->failure;
        }

        return $this->place !== null && $this->place->id()->equals($id) ? $this->place : null;
    }

    public function add(Place $place): void
    {
        throw new RuntimeException('Mutation is forbidden.');
    }

    public function save(Place $place, int $expectedVersion): void
    {
        throw new ConcurrentPlaceModification;
    }
}
