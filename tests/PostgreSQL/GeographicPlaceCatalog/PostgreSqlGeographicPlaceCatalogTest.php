<?php

namespace Tests\PostgreSQL\GeographicPlaceCatalog;

use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Appart\Modules\RealEstateCatalog\Application\UseCase\ChangeAddress;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Domain\Exception\UnavailableGeographicPlace;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\RealEstateCatalog\Support\FakePropertyRegistry;

final class PostgreSqlGeographicPlaceCatalogTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPlaceRepository $places;

    private GeographyBackedGeographicPlaceCatalog $catalog;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->places = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $this->catalog = new GeographyBackedGeographicPlaceCatalog($this->places, new GeographicPlaceAddressabilityPolicy);
    }

    public function test_real_f0_places_cover_usable_disabled_merged_and_missing(): void
    {
        [$country, $region] = $this->roots('21');
        $city = $this->city('21000000-0000-4000-8000-000000000003', 'Dakar', 'DKR', $region);
        $disabled = $this->city('21000000-0000-4000-8000-000000000004', 'Disabled City', 'DIS', $region);
        $target = $this->city('21000000-0000-4000-8000-000000000005', 'Target City', 'TGT', $region);
        $merged = $this->city('21000000-0000-4000-8000-000000000006', 'Merged City', 'MRG', $region);
        foreach ([$city, $disabled, $target, $merged] as $place) {
            $this->places->add($place);
        }
        $disabled->disable($this->at('+1 second'));
        $this->places->save($disabled, 1);
        $merged->mergeInto($target, $this->at('+1 second'));
        $this->places->save($merged, 1);

        self::assertSame(GeographicPlaceStatus::Usable, $this->observedStatus($city));
        self::assertSame(GeographicPlaceStatus::Disabled, $this->observedStatus($disabled));
        self::assertSame(GeographicPlaceStatus::Merged, $this->observedStatus($merged));
        self::assertSame(GeographicPlaceStatus::NotFound, $this->catalog->statusOf($this->geographicId('21000000-0000-4000-8000-000000000099')));
        self::assertSame(GeographicPlaceStatus::NotAddressable, $this->observedStatus($country));
    }

    public function test_register_and_change_address_compose_with_productive_catalog_without_catalog_fake(): void
    {
        [$country, $region] = $this->roots('22');
        $dakar = $this->city('22000000-0000-4000-8000-000000000003', 'Dakar', 'DKR2', $region);
        $thies = $this->city('22000000-0000-4000-8000-000000000004', 'Thies', 'THI', $region);
        $this->places->add($dakar);
        $this->places->add($thies);
        $properties = new FakePropertyRegistry;
        $id = PropertyId::fromString('52000000-0000-4000-8000-000000000001');

        $registered = (new RegisterProperty($properties, $this->catalog, new PropertyTypePolicy))->execute(
            $id,
            PropertyReference::fromString('PROP-F5A-001'),
            PropertyType::Apartment,
            SurfaceArea::fromSquareMeters(80),
            RoomCount::fromInt(3),
            BathroomCount::fromInt(1),
            null,
            $this->address('52000000-0000-4000-8000-000000000002', $dakar, '12 avenue Dakar'),
            BusinessYear::fromInt(2026),
            $this->at(),
        );
        self::assertSame($dakar->id()->value, $registered->address()?->placeId->value);

        $changed = (new ChangeAddress($properties, $this->catalog))->execute(
            $id,
            $this->address('52000000-0000-4000-8000-000000000003', $thies, '8 avenue Thies'),
            $this->at('+1 second'),
        );
        self::assertSame($thies->id()->value, $changed->address()?->placeId->value);

        try {
            (new RegisterProperty(new FakePropertyRegistry, $this->catalog, new PropertyTypePolicy))->execute(
                PropertyId::fromString('52000000-0000-4000-8000-000000000004'),
                PropertyReference::fromString('PROP-F5A-002'),
                PropertyType::Apartment,
                SurfaceArea::fromSquareMeters(80),
                RoomCount::fromInt(3),
                BathroomCount::fromInt(1),
                null,
                $this->address('52000000-0000-4000-8000-000000000005', $country, 'Senegal'),
                BusinessYear::fromInt(2026),
                $this->at(),
            );
            self::fail('Country must be refused.');
        } catch (UnavailableGeographicPlace $error) {
            self::assertSame(GeographicPlaceStatus::NotAddressable, $error->status);
        }
    }

    /** @return array{Place, Place} */
    private function roots(string $prefix): array
    {
        $country = Place::create(PlaceId::fromString($prefix.'000000-0000-4000-8000-000000000001'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $this->at());
        $region = Place::create(PlaceId::fromString($prefix.'000000-0000-4000-8000-000000000002'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('REG'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $country);
        $this->places->add($country);
        $this->places->add($region);

        return [$country, $region];
    }

    private function city(string $id, string $name, string $code, Place $region): Place
    {
        return Place::create(PlaceId::fromString($id), PlaceName::fromString($name), PlaceCode::fromString($code), PlaceType::City, CountryCode::fromString('SN'), $this->at(), $region);
    }

    private function observedStatus(Place $place): GeographicPlaceStatus
    {
        return $this->catalog->statusOf($this->geographicId($place->id()->value));
    }

    private function address(string $id, Place $place, string $line): Address
    {
        return new Address(AddressId::fromString($id), $this->geographicId($place->id()->value), AddressLine::fromString($line));
    }

    private function geographicId(string $id): GeographicPlaceId
    {
        return GeographicPlaceId::fromString($id);
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-14T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}
