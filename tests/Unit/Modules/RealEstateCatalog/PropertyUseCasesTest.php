<?php

namespace Tests\Unit\Modules\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Application\UseCase\ArchiveProperty;
use Appart\Modules\RealEstateCatalog\Application\UseCase\ChangeAddress;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Application\UseCase\UpdateProperty;
use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyNotFound;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\UnavailableGeographicPlace;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\RealEstateCatalog\Support\FakeGeographicPlaceCatalog;
use Tests\Unit\Modules\RealEstateCatalog\Support\FakePropertyRegistry;

final class PropertyUseCasesTest extends TestCase
{
    public function test_use_cases_orchestrate_update_address_and_archive(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);
        (new UpdateProperty($r, $this->policy()))->execute($this->id(), PropertyType::Villa, SurfaceArea::fromSquareMeters(150), RoomCount::fromInt(6), BathroomCount::fromInt(3), ConstructionYear::fromInt(2020), $this->businessYear(), $this->now());
        (new ChangeAddress($r, $this->places()))->execute($this->id(), $this->otherAddress(), $this->now());
        (new ArchiveProperty($r))->execute($this->id(), $this->now());
        $stored = $r->find($this->id());
        self::assertNotNull($stored);
        self::assertSame(PropertyStatus::Archived, $stored->status());
        self::assertSame(3, $stored->version());
    }

    public function test_duplicate_property_id_is_explicit(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);
        $this->expectException(PropertyIdConflict::class);
        $this->register($r);
    }

    public function test_duplicate_reference_is_explicit(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);
        $this->expectException(PropertyReferenceConflict::class);
        (new RegisterProperty($r, $this->places(), $this->policy()))->execute(PropertyId::fromString('50000000-0000-4000-8000-000000000099'), $this->reference(), PropertyType::House, $this->surface(), RoomCount::fromInt(3), BathroomCount::fromInt(1), null, $this->address(), $this->businessYear(), $this->now());
    }

    public function test_unknown_property_is_reported(): void
    {
        $this->expectException(PropertyNotFound::class);
        (new ArchiveProperty(new FakePropertyRegistry))->execute($this->id(), $this->now());
    }

    public function test_stale_save_is_rejected(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);
        $first = $r->find($this->id());
        $stale = $r->find($this->id());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $first->archive($this->now());
        $r->save($first, 0);
        $stale->changeAddress($this->otherAddress(), $this->now());
        $this->expectException(ConcurrentPropertyModification::class);
        $r->save($stale, 0);
    }

    public function test_failed_save_leaves_no_visible_mutation(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);
        $r->failNextSave();
        try {
            (new ArchiveProperty($r))->execute($this->id(), $this->now());
            self::fail();
        } catch (ConcurrentPropertyModification) {
            $stored = $r->find($this->id());
            self::assertNotNull($stored);
            self::assertSame(PropertyStatus::Active, $stored->status());
            self::assertSame(0, $stored->version());
        }
    }

    public function test_reloaded_property_never_replays_events(): void
    {
        $r = new FakePropertyRegistry;
        $created = $this->register($r);
        self::assertCount(1, $created->releaseEvents());
        self::assertSame([], $r->find($this->id())?->releaseEvents());
        $changed = (new ArchiveProperty($r))->execute($this->id(), $this->now());
        self::assertCount(1, $changed->releaseEvents());
        self::assertSame([], $r->find($this->id())?->releaseEvents());
    }

    public function test_registration_rejects_an_unavailable_geographic_place(): void
    {
        $this->expectException(UnavailableGeographicPlace::class);
        (new RegisterProperty(new FakePropertyRegistry, new FakeGeographicPlaceCatalog([]), $this->policy()))->execute($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), null, $this->address(), $this->businessYear(), $this->now());
    }

    public function test_address_change_rejects_an_unavailable_place_without_mutation(): void
    {
        $r = new FakePropertyRegistry;
        $this->register($r);

        try {
            (new ChangeAddress($r, new FakeGeographicPlaceCatalog([])))->execute($this->id(), $this->otherAddress(), $this->now());
            self::fail();
        } catch (UnavailableGeographicPlace) {
            self::assertTrue($r->find($this->id())?->address()?->equals($this->address()));
        }
    }

    public function test_merged_place_is_explicitly_rejected(): void
    {
        $places = new FakeGeographicPlaceCatalog(['place:dakar' => GeographicPlaceStatus::Merged]);
        try {
            (new RegisterProperty(new FakePropertyRegistry, $places, $this->policy()))->execute($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), null, $this->address(), $this->businessYear(), $this->now());
            self::fail();
        } catch (UnavailableGeographicPlace $exception) {
            self::assertSame(GeographicPlaceStatus::Merged, $exception->status);
        }
    }

    public function test_disabled_place_is_explicitly_rejected(): void
    {
        $places = new FakeGeographicPlaceCatalog(['place:dakar' => GeographicPlaceStatus::Disabled]);
        try {
            (new RegisterProperty(new FakePropertyRegistry, $places, $this->policy()))->execute($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), null, $this->address(), $this->businessYear(), $this->now());
            self::fail();
        } catch (UnavailableGeographicPlace $exception) {
            self::assertSame(GeographicPlaceStatus::Disabled, $exception->status);
        }
    }

    private function register(FakePropertyRegistry $r): Property
    {
        return (new RegisterProperty($r, $this->places(), $this->policy()))->execute($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2018), $this->address(), $this->businessYear(), $this->now());
    }

    private function places(): FakeGeographicPlaceCatalog
    {
        return new FakeGeographicPlaceCatalog;
    }

    private function policy(): PropertyTypePolicy
    {
        return new PropertyTypePolicy;
    }

    private function businessYear(): BusinessYear
    {
        return BusinessYear::fromInt(2026);
    }

    private function id(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function reference(): PropertyReference
    {
        return PropertyReference::fromString('PROP-DKR-001');
    }

    private function surface(): SurfaceArea
    {
        return SurfaceArea::fromSquareMeters(120);
    }

    private function address(): Address
    {
        return new Address(AddressId::fromString('50000000-0000-4000-8000-000000000002'), GeographicPlaceId::fromString('place:dakar'), AddressLine::fromString('12 avenue du Sénégal'));
    }

    private function otherAddress(): Address
    {
        return new Address(AddressId::fromString('50000000-0000-4000-8000-000000000003'), GeographicPlaceId::fromString('place:thies'), AddressLine::fromString('8 avenue Lat Dior'));
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }
}
