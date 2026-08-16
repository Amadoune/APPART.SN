<?php

namespace Tests\PostgreSQL\PublicPropertyPromotion;

use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Application\Promotion\DeterministicPromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyCommand;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyStatus;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromotionCommandRecord;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionParticipantTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicPropertyPromotionTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPropertyAuthoringStore $authoring;

    private PostgreSqlPlaceRepository $places;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->authoring = new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper);
        $this->places = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $this->seedGeography();
    }

    public function test_applied_replay_divergence_and_single_property_are_durable(): void
    {
        $this->save($this->complete());
        $runtime = $this->runtime();
        $command = $this->command();

        self::assertSame(PromoteAuthoredPropertyStatus::Applied, $runtime->promote($command)->status);
        self::assertSame(PromoteAuthoredPropertyStatus::AlreadyApplied, $runtime->promote($command)->status);
        self::assertSame(PromoteAuthoredPropertyStatus::DivergentCommand, $runtime->promote($this->command('+1 second'))->status);
        self::assertSame(1, $this->rowCount('real_estate_catalog.properties'));
        self::assertSame(1, $this->rowCount('real_estate_catalog.property_promotion_commands'));
        self::assertNotNull($this->properties()->find(PropertyId::fromString($this->complete()->propertyId)));
    }

    public function test_owner_version_and_incomplete_snapshot_fail_without_mutation(): void
    {
        $this->save($this->complete());
        $runtime = $this->runtime();

        self::assertSame(PromoteAuthoredPropertyStatus::OwnershipMismatch, $runtime->promote(new PromoteAuthoredPropertyCommand($this->property(), '61000000-0000-4000-8000-000000000099', 1, $this->commandId(), $this->at()))->status);
        self::assertSame(PromoteAuthoredPropertyStatus::VersionConflict, $runtime->promote(new PromoteAuthoredPropertyCommand($this->property(), $this->owner(), 2, $this->commandId(), $this->at()))->status);

        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedGeography();
        $this->save(new PropertyAuthoringState($this->property(), $this->owner(), 1, $this->intent(), str_repeat('a', 64), propertyType: 'apartment'));
        self::assertSame(PromoteAuthoredPropertyStatus::IncompleteAuthoring, $this->runtime()->promote($this->command())->status);
        self::assertSame(0, $this->rowCount('real_estate_catalog.properties'));
        self::assertSame(0, $this->rowCount('real_estate_catalog.property_promotion_commands'));
    }

    public function test_negative_geography_and_ledger_failure_roll_back_everything(): void
    {
        $this->save($this->complete());
        $city = $this->places->find(PlaceId::fromString($this->place()));
        self::assertNotNull($city);
        $city->disable($this->at('+1 second'));
        $this->places->save($city, 1);
        self::assertSame(PromoteAuthoredPropertyStatus::DomainRejected, $this->runtime()->promote($this->command())->status);
        self::assertSame(0, $this->rowCount('real_estate_catalog.properties'));

        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedGeography();
        $this->save($this->complete());
        $failing = new class implements PromotionCommandLedger
        {
            public function find(string $commandId): ?PromotionCommandRecord
            {
                return null;
            }

            public function record(PromotionCommandRecord $record): void
            {
                throw new RuntimeException('ledger unavailable');
            }
        };
        self::assertSame(PromoteAuthoredPropertyStatus::DependencyUnavailable, $this->runtime($failing)->promote($this->command())->status);
        self::assertSame(0, $this->rowCount('real_estate_catalog.properties'));
        self::assertSame(0, $this->rowCount('real_estate_catalog.property_promotion_commands'));
    }

    public function test_ledgerless_existing_property_requires_canonical_address_identity(): void
    {
        $this->save($this->complete());
        self::assertSame(PromoteAuthoredPropertyStatus::Applied, $this->runtime()->promote($this->command())->status);
        $catchUp = new PromoteAuthoredPropertyCommand($this->property(), $this->owner(), 1, '61000000-0000-4000-8000-000000000006', $this->at());
        self::assertSame(PromoteAuthoredPropertyStatus::AlreadyApplied, $this->runtime()->promote($catchUp)->status);
        self::assertSame(1, $this->rowCount('real_estate_catalog.properties'));
        self::assertSame(1, $this->rowCount('real_estate_catalog.property_promotion_commands'));

        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedGeography();
        $this->save($this->complete());
        $nonCanonicalId = AddressId::fromString('61000000-0000-4000-8000-000000000099');
        $repository = new PostgreSqlPropertyRepository($this->connection, new PropertyMapper, new PostgreSqlPropertyTransaction($this->connection));
        $repository->add($this->existing($nonCanonicalId));

        self::assertSame(PromoteAuthoredPropertyStatus::DivergentCommand, $this->runtime()->promote($this->command())->status);
        self::assertSame(1, $this->rowCount('real_estate_catalog.properties'));
        self::assertSame(0, $this->rowCount('real_estate_catalog.property_promotion_commands'));
        self::assertSame($nonCanonicalId->value, $repository->find(PropertyId::fromString($this->property()))?->address()?->id->value);
    }

    private function runtime(?PromotionCommandLedger $ledger = null): DeterministicPromoteAuthoredPropertyV1
    {
        $properties = $this->properties();
        $catalog = new GeographyBackedGeographicPlaceCatalog($this->places, new GeographicPlaceAddressabilityPolicy);

        return new DeterministicPromoteAuthoredPropertyV1(
            $this->authoring,
            $properties,
            new RegisterProperty($properties, $catalog, new PropertyTypePolicy),
            new DeterministicAddressIdentityIssuerV1,
            new UtcCalendarBusinessYearAuthorityV1,
            $ledger ?? new PostgreSqlPromotionCommandLedger($this->connection),
            new PostgreSqlPromotionTransaction($this->connection),
        );
    }

    private function properties(): PostgreSqlPropertyRepository
    {
        return new PostgreSqlPropertyRepository($this->connection, new PropertyMapper, new PostgreSqlPromotionParticipantTransaction($this->connection));
    }

    private function save(PropertyAuthoringState $state): void
    {
        $this->authoring->save($state, 0);
    }

    private function complete(): PropertyAuthoringState
    {
        return new PropertyAuthoringState($this->property(), $this->owner(), 1, $this->intent(), str_repeat('a', 64), 'apartment', 'Dakar', 'Plateau', 'PROP-F6-001', 120, 4, 2, 2020, $this->place(), '12 avenue Dakar', '61000000-0000-4000-8000-000000000004');
    }

    private function existing(AddressId $addressId): Property
    {
        return Property::reconstitute(
            PropertyId::fromString($this->property()),
            PropertyReference::fromString('PROP-F6-001'),
            PropertyType::Apartment,
            SurfaceArea::fromSquareMeters(120),
            RoomCount::fromInt(4),
            BathroomCount::fromInt(2),
            ConstructionYear::fromInt(2020),
            new Address($addressId, GeographicPlaceId::fromString($this->place()), AddressLine::fromString('12 avenue Dakar')),
            PropertyStatus::Active,
            $this->at(),
            0,
        );
    }

    private function command(string $modifier = ''): PromoteAuthoredPropertyCommand
    {
        return new PromoteAuthoredPropertyCommand($this->property(), $this->owner(), 1, $this->commandId(), $this->at($modifier));
    }

    private function seedGeography(): void
    {
        $country = Place::create(PlaceId::fromString('61000000-0000-4000-8000-000000000011'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $this->at());
        $region = Place::create(PlaceId::fromString('61000000-0000-4000-8000-000000000012'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $country);
        $city = Place::create(PlaceId::fromString($this->place()), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKC'), PlaceType::City, CountryCode::fromString('SN'), $this->at(), $region);
        foreach ([$country, $region, $city] as $place) {
            $this->places->add($place);
        }
    }

    private function rowCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }

    private function property(): string
    {
        return '61000000-0000-4000-8000-000000000001';
    }

    private function owner(): string
    {
        return '61000000-0000-4000-8000-000000000002';
    }

    private function intent(): string
    {
        return '61000000-0000-4000-8000-000000000003';
    }

    private function commandId(): string
    {
        return '61000000-0000-4000-8000-000000000005';
    }

    private function place(): string
    {
        return '61000000-0000-4000-8000-000000000013';
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-14T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}
