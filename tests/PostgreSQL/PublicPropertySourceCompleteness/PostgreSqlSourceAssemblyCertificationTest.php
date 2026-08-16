<?php

namespace Tests\PostgreSQL\PublicPropertySourceCompleteness;

use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringSourceCompleteness;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\PropertyDecisionOccurredAt;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSourceAssemblyCertificationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_complete_authoring_snapshot_assembles_every_register_property_input_without_promotion(): void
    {
        $snapshot = new PropertyAuthoringState(
            propertyId: '53000000-0000-4000-8000-000000000001',
            ownerAccountId: '53000000-0000-4000-8000-000000000002',
            version: 1,
            intentId: '53000000-0000-4000-8000-000000000003',
            intentChecksum: str_repeat('a', 64),
            propertyType: 'apartment',
            propertyReference: 'PROP-F5-001',
            surfaceSquareMeters: 120,
            rooms: 4,
            bathrooms: 2,
            constructionYear: 2020,
            geographicPlaceId: '53000000-0000-4000-8000-000000000013',
            addressLine: '12 avenue Cheikh Anta Diop',
            addressIntentId: '53000000-0000-4000-8000-000000000004',
        );
        self::assertSame(PropertyAuthoringSourceCompleteness::CompleteForPromotion, $snapshot->completeness());

        $propertyId = PropertyId::fromString($snapshot->propertyId);
        $reference = PropertyReference::fromString((string) $snapshot->propertyReference);
        $type = PropertyType::from((string) $snapshot->propertyType);
        $surface = SurfaceArea::fromSquareMeters((int) $snapshot->surfaceSquareMeters);
        $rooms = RoomCount::fromInt((int) $snapshot->rooms);
        $bathrooms = BathroomCount::fromInt((int) $snapshot->bathrooms);
        $year = ConstructionYear::fromInt((int) $snapshot->constructionYear);
        $placeId = GeographicPlaceId::fromString((string) $snapshot->geographicPlaceId);
        $line = AddressLine::fromString((string) $snapshot->addressLine);
        $addressId = (new DeterministicAddressIdentityIssuerV1)->issue($propertyId, AddressIntentId::fromString((string) $snapshot->addressIntentId))->addressId;
        $businessYear = (new UtcCalendarBusinessYearAuthorityV1)->resolve(PropertyDecisionOccurredAt::fromString('2026-08-14T10:00:00.000000Z'))->businessYear;
        $address = new Address($addressId, $placeId, $line);

        $repository = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        [$country, $region, $city] = $this->geography();
        foreach ([$country, $region, $city] as $place) {
            $repository->add($place);
        }
        $catalog = new GeographyBackedGeographicPlaceCatalog($repository, new GeographicPlaceAddressabilityPolicy);

        self::assertSame(GeographicPlaceStatus::Usable, $catalog->statusOf($placeId));
        self::assertSame('PROP-F5-001', $reference->value);
        self::assertSame(PropertyType::Apartment, $type);
        self::assertSame(120, $surface->squareMeters);
        self::assertSame(4, $rooms->value);
        self::assertSame(2, $bathrooms->value);
        self::assertSame(2020, $year->value);
        self::assertSame($snapshot->geographicPlaceId, $address->placeId->value);
        self::assertSame('12 avenue Cheikh Anta Diop', $address->line->value);
        self::assertSame(2026, $businessYear->value);
        self::assertSame($addressId->value, (new DeterministicAddressIdentityIssuerV1)->issue($propertyId, AddressIntentId::fromString((string) $snapshot->addressIntentId))->addressId->value);
    }

    /** @return array{Place, Place, Place} */
    private function geography(): array
    {
        $at = PropertyDecisionOccurredAt::fromString('2026-08-14T10:00:00.000000Z')->instant();
        $country = Place::create(PlaceId::fromString('53000000-0000-4000-8000-000000000011'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $at);
        $region = Place::create(PlaceId::fromString('53000000-0000-4000-8000-000000000012'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $at, $country);
        $city = Place::create(PlaceId::fromString('53000000-0000-4000-8000-000000000013'), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKC'), PlaceType::City, CountryCode::fromString('SN'), $at, $region);

        return [$country, $region, $city];
    }
}
