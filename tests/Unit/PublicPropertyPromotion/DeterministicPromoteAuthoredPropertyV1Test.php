<?php

namespace Tests\Unit\PublicPropertyPromotion;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionTransaction;
use Appart\Modules\RealEstateCatalog\Application\Promotion\DeterministicPromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyCommand;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyStatus;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromotionCommandChecksum;
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
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\RealEstateCatalog\Support\FakePropertyRegistry;

final class DeterministicPromoteAuthoredPropertyV1Test extends TestCase
{
    public function test_applied_replay_divergence_checksum_address_and_business_year_are_deterministic(): void
    {
        $state = $this->complete();
        $properties = new FakePropertyRegistry;
        $ledger = new MemoryPromotionLedger;
        $runtime = $this->runtime(new MemoryAuthoringStore($state), $properties, $ledger, $this->city());
        $command = $this->command();

        self::assertSame(PromotionCommandChecksum::for($command, $state), PromotionCommandChecksum::for($command, $state));
        self::assertSame(PromoteAuthoredPropertyStatus::Applied, $runtime->promote($command)->status);
        self::assertSame(PromoteAuthoredPropertyStatus::AlreadyApplied, $runtime->promote($command)->status);
        self::assertSame(PromoteAuthoredPropertyStatus::DivergentCommand, $runtime->promote($this->command('+1 second'))->status);
        $property = $properties->find(PropertyId::fromString($state->propertyId));
        self::assertNotNull($property);
        self::assertSame('2026', $property->lastChangedAt()->format('Y'));
        self::assertSame($property->address()?->id->value, $properties->find($property->id())?->address()?->id->value);
    }

    public function test_missing_owner_version_and_incomplete_are_closed_without_property(): void
    {
        $properties = new FakePropertyRegistry;
        self::assertSame(PromoteAuthoredPropertyStatus::AuthoringMissing, $this->runtime(new MemoryAuthoringStore, $properties, new MemoryPromotionLedger, $this->city())->promote($this->command())->status);
        self::assertSame(PromoteAuthoredPropertyStatus::OwnershipMismatch, $this->runtime(new MemoryAuthoringStore($this->complete()), $properties, new MemoryPromotionLedger, $this->city())->promote(new PromoteAuthoredPropertyCommand($this->property(), '62000000-0000-4000-8000-000000000099', 1, $this->commandId(), $this->at()))->status);
        self::assertSame(PromoteAuthoredPropertyStatus::VersionConflict, $this->runtime(new MemoryAuthoringStore($this->complete()), $properties, new MemoryPromotionLedger, $this->city())->promote(new PromoteAuthoredPropertyCommand($this->property(), $this->owner(), 2, $this->commandId(), $this->at()))->status);
        $incomplete = new PropertyAuthoringState($this->property(), $this->owner(), 1, $this->intent(), str_repeat('a', 64), propertyType: 'apartment');
        self::assertSame(PromoteAuthoredPropertyStatus::IncompleteAuthoring, $this->runtime(new MemoryAuthoringStore($incomplete), $properties, new MemoryPromotionLedger, $this->city())->promote($this->command())->status);
        self::assertNull($properties->find(PropertyId::fromString($this->property())));
    }

    public function test_non_addressable_geography_is_domain_rejected_without_fallback(): void
    {
        $properties = new FakePropertyRegistry;
        self::assertSame(PromoteAuthoredPropertyStatus::DomainRejected, $this->runtime(new MemoryAuthoringStore($this->complete()), $properties, new MemoryPromotionLedger, $this->country())->promote($this->command())->status);
        self::assertNull($properties->find(PropertyId::fromString($this->property())));
    }

    public function test_ledgerless_existing_property_requires_complete_canonical_compatibility(): void
    {
        $compatible = new FakePropertyRegistry;
        $compatible->add($this->existing());
        $ledger = new MemoryPromotionLedger;
        self::assertSame(PromoteAuthoredPropertyStatus::AlreadyApplied, $this->runtime(new MemoryAuthoringStore($this->complete()), $compatible, $ledger, $this->city())->promote($this->command())->status);
        self::assertNull($ledger->recorded());

        $divergences = [
            'address id' => ['addressId' => '62000000-0000-4000-8000-000000000099'],
            'place id' => ['placeId' => '62000000-0000-4000-8000-000000000098'],
            'address line' => ['addressLine' => '99 avenue Dakar'],
            'missing address' => ['withoutAddress' => true],
            'reference' => ['reference' => 'PROP-F6-OTHER'],
            'type' => ['type' => PropertyType::House],
            'surface' => ['surface' => 121],
            'rooms' => ['rooms' => 5],
            'bathrooms' => ['bathrooms' => 3],
            'construction year' => ['constructionYear' => 2019],
            'aggregate version' => ['version' => 1],
        ];
        foreach ($divergences as $label => $overrides) {
            $properties = new FakePropertyRegistry;
            $properties->add($this->existing($overrides));
            $caseLedger = new MemoryPromotionLedger;
            self::assertSame(PromoteAuthoredPropertyStatus::DivergentCommand, $this->runtime(new MemoryAuthoringStore($this->complete()), $properties, $caseLedger, $this->city())->promote($this->command())->status, $label);
            self::assertNull($caseLedger->recorded(), $label);
            $expectedAddressId = ($overrides['withoutAddress'] ?? false) === true ? null : ($overrides['addressId'] ?? $this->canonicalAddress()->id->value);
            self::assertSame($expectedAddressId, $properties->find(PropertyId::fromString($this->property()))?->address()?->id->value, $label);
        }
    }

    public function test_address_presence_matrix_is_explicit(): void
    {
        $runtime = $this->runtime(new MemoryAuthoringStore($this->complete()), new FakePropertyRegistry, new MemoryPromotionLedger, $this->city());
        $method = new \ReflectionMethod($runtime, 'addressesCompatible');
        $address = $this->canonicalAddress();

        self::assertTrue($method->invoke($runtime, null, null));
        self::assertFalse($method->invoke($runtime, $address, null));
        self::assertFalse($method->invoke($runtime, null, $address));
        self::assertTrue($method->invoke($runtime, $address, $address));
    }

    private function runtime(MemoryAuthoringStore $authoring, FakePropertyRegistry $properties, MemoryPromotionLedger $ledger, Place $place): DeterministicPromoteAuthoredPropertyV1
    {
        $places = new MemoryPlaceRegistry($place);
        $catalog = new GeographyBackedGeographicPlaceCatalog($places, new GeographicPlaceAddressabilityPolicy);

        return new DeterministicPromoteAuthoredPropertyV1($authoring, $properties, new RegisterProperty($properties, $catalog, new PropertyTypePolicy), new DeterministicAddressIdentityIssuerV1, new UtcCalendarBusinessYearAuthorityV1, $ledger, new MemoryPromotionTransaction);
    }

    private function complete(): PropertyAuthoringState
    {
        return new PropertyAuthoringState($this->property(), $this->owner(), 1, $this->intent(), str_repeat('a', 64), 'apartment', 'Dakar', 'Plateau', 'PROP-F6-U01', 120, 4, 2, 2020, $this->place(), '12 avenue Dakar', '62000000-0000-4000-8000-000000000004');
    }

    /** @param array<string, mixed> $overrides */
    private function existing(array $overrides = []): Property
    {
        $address = ($overrides['withoutAddress'] ?? false) === true ? null : new Address(
            AddressId::fromString((string) ($overrides['addressId'] ?? $this->canonicalAddress()->id->value)),
            GeographicPlaceId::fromString((string) ($overrides['placeId'] ?? $this->place())),
            AddressLine::fromString((string) ($overrides['addressLine'] ?? '12 avenue Dakar')),
        );

        return Property::reconstitute(
            PropertyId::fromString($this->property()),
            PropertyReference::fromString((string) ($overrides['reference'] ?? 'PROP-F6-U01')),
            $overrides['type'] ?? PropertyType::Apartment,
            SurfaceArea::fromSquareMeters((int) ($overrides['surface'] ?? 120)),
            RoomCount::fromInt((int) ($overrides['rooms'] ?? 4)),
            BathroomCount::fromInt((int) ($overrides['bathrooms'] ?? 2)),
            ConstructionYear::fromInt((int) ($overrides['constructionYear'] ?? 2020)),
            $address,
            PropertyStatus::Active,
            $this->at(),
            (int) ($overrides['version'] ?? 0),
        );
    }

    private function canonicalAddress(): Address
    {
        $id = (new DeterministicAddressIdentityIssuerV1)->issue(
            PropertyId::fromString($this->property()),
            AddressIntentId::fromString('62000000-0000-4000-8000-000000000004'),
        )->addressId;

        return new Address($id, GeographicPlaceId::fromString($this->place()), AddressLine::fromString('12 avenue Dakar'));
    }

    private function city(): Place
    {
        $country = $this->country('62000000-0000-4000-8000-000000000011');
        $region = Place::create(PlaceId::fromString('62000000-0000-4000-8000-000000000012'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('REG'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $country);

        return Place::create(PlaceId::fromString($this->place()), PlaceName::fromString('Dakar'), PlaceCode::fromString('CITY'), PlaceType::City, CountryCode::fromString('SN'), $this->at(), $region);
    }

    private function country(string $id = '62000000-0000-4000-8000-000000000013'): Place
    {
        return Place::create(PlaceId::fromString($id), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $this->at());
    }

    private function command(string $modifier = ''): PromoteAuthoredPropertyCommand
    {
        return new PromoteAuthoredPropertyCommand($this->property(), $this->owner(), 1, $this->commandId(), $this->at($modifier));
    }

    private function property(): string
    {
        return '62000000-0000-4000-8000-000000000001';
    }

    private function owner(): string
    {
        return '62000000-0000-4000-8000-000000000002';
    }

    private function intent(): string
    {
        return '62000000-0000-4000-8000-000000000003';
    }

    private function commandId(): string
    {
        return '62000000-0000-4000-8000-000000000005';
    }

    private function place(): string
    {
        return '62000000-0000-4000-8000-000000000013';
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-14T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}

final class MemoryAuthoringStore implements PropertyAuthoringStore
{
    public function __construct(private ?PropertyAuthoringState $state = null) {}

    public function read(string $propertyId): ?PropertyAuthoringState
    {
        return $this->state;
    }

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult
    {
        return PropertyAuthoringPersistenceWriteResult::Rejected;
    }
}

final class MemoryPromotionLedger implements PromotionCommandLedger
{
    private ?PromotionCommandRecord $record = null;

    public function find(string $commandId): ?PromotionCommandRecord
    {
        return $this->record;
    }

    public function record(PromotionCommandRecord $record): void
    {
        $this->record = $record;
    }

    public function recorded(): ?PromotionCommandRecord
    {
        return $this->record;
    }
}

final class MemoryPromotionTransaction implements PromotionTransaction
{
    public function run(string $commandId, string $propertyId, Closure $operation): mixed
    {
        return $operation();
    }
}

final class MemoryPlaceRegistry implements PlaceRegistry
{
    public function __construct(private Place $place) {}

    public function find(PlaceId $id): ?Place
    {
        return $this->place->id()->equals($id) ? $this->place : null;
    }

    public function add(Place $place): void {}

    public function save(Place $place, int $expectedVersion): void {}
}
