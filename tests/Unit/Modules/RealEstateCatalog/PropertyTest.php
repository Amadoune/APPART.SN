<?php

namespace Tests\Unit\Modules\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Domain\Event\AddressChanged;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyArchived;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyRegistered;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyUpdated;
use Appart\Modules\RealEstateCatalog\Domain\Event\SurfaceChanged;
use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyViolation;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PropertyTest extends TestCase
{
    public function test_it_registers_a_property_with_stable_identity_and_event(): void
    {
        $p = $this->property();
        $events = $p->releaseEvents();
        self::assertSame($this->id()->value, $p->id()->value);
        self::assertSame(PropertyStatus::Active, $p->status());
        self::assertInstanceOf(PropertyRegistered::class, $events[0]);
    }

    public function test_it_updates_descriptive_details_and_surface_events_in_order(): void
    {
        $p = $this->property();
        $p->releaseEvents();
        $p->update(PropertyType::Villa, SurfaceArea::fromSquareMeters(150), RoomCount::fromInt(6), BathroomCount::fromInt(3), ConstructionYear::fromInt(2020), $this->businessYear(), $this->policy(), $this->later());
        $events = $p->releaseEvents();
        self::assertSame(150, $p->surface()?->squareMeters);
        self::assertInstanceOf(PropertyUpdated::class, $events[0]);
        self::assertInstanceOf(SurfaceChanged::class, $events[1]);
        self::assertSame(120, $events[1]->previousSurface->squareMeters);
        self::assertSame(150, $events[1]->newSurface->squareMeters);
    }

    public function test_update_without_surface_change_emits_only_property_updated(): void
    {
        $p = $this->property();
        $p->releaseEvents();
        $p->update(PropertyType::Villa, $p->surface(), $p->rooms(), $p->bathrooms(), $p->constructionYear(), $this->businessYear(), $this->policy(), $this->later());
        self::assertCount(1, $p->releaseEvents());
    }

    public function test_noop_update_is_rejected(): void
    {
        $p = $this->property();
        $this->expectException(PropertyViolation::class);
        $p->update($p->type(), $p->surface(), $p->rooms(), $p->bathrooms(), $p->constructionYear(), $this->businessYear(), $this->policy(), $this->now());
    }

    public function test_room_dependencies_must_be_coherent(): void
    {
        $this->expectException(PropertyViolation::class);
        Property::register($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(2), BathroomCount::fromInt(3), null, $this->address(), $this->businessYear(), $this->policy(), $this->now());
    }

    public function test_future_construction_year_is_rejected_against_business_year(): void
    {
        $this->expectException(PropertyViolation::class);
        Property::register($this->id(), $this->reference(), PropertyType::House, $this->surface(), RoomCount::fromInt(3), BathroomCount::fromInt(1), ConstructionYear::fromInt(2027), $this->address(), $this->businessYear(), $this->policy(), $this->now());
    }

    public function test_land_has_explicit_zero_room_zero_bathroom_and_no_year_policy(): void
    {
        $land = Property::register($this->id(), $this->reference(), PropertyType::Land, $this->surface(), RoomCount::fromInt(0), BathroomCount::fromInt(0), null, $this->address(), $this->businessYear(), $this->policy(), $this->now());

        self::assertSame(PropertyType::Land, $land->type());
        self::assertSame(0, $land->rooms()->value);
    }

    public function test_land_rejects_construction_details(): void
    {
        $this->expectException(PropertyViolation::class);
        Property::register($this->id(), $this->reference(), PropertyType::Land, $this->surface(), RoomCount::fromInt(1), BathroomCount::fromInt(0), ConstructionYear::fromInt(2020), $this->address(), $this->businessYear(), $this->policy(), $this->now());
    }

    public function test_apartment_without_room_is_rejected(): void
    {
        $this->expectException(PropertyViolation::class);
        Property::register($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(0), BathroomCount::fromInt(0), null, $this->address(), $this->businessYear(), $this->policy(), $this->now());
    }

    public function test_office_requires_rooms_and_accepts_coherent_bathrooms(): void
    {
        $office = Property::register($this->id(), $this->reference(), PropertyType::Office, $this->surface(), RoomCount::fromInt(4), BathroomCount::fromInt(1), ConstructionYear::fromInt(2024), $this->address(), $this->businessYear(), $this->policy(), $this->now());

        self::assertSame(PropertyType::Office, $office->type());
    }

    public function test_commercial_property_allows_facilities_independent_of_room_count_ratio(): void
    {
        $commercial = Property::register($this->id(), $this->reference(), PropertyType::Commercial, $this->surface(), RoomCount::fromInt(1), BathroomCount::fromInt(3), null, $this->address(), $this->businessYear(), $this->policy(), $this->now());

        self::assertSame(3, $commercial->bathrooms()->value);
    }

    public function test_other_type_may_be_registered_without_surface_or_address(): void
    {
        $other = Property::register($this->id(), $this->reference(), PropertyType::Other, null, RoomCount::fromInt(0), BathroomCount::fromInt(0), null, null, $this->businessYear(), $this->policy(), $this->now());

        self::assertNull($other->surface());
        self::assertNull($other->address());
    }

    public function test_it_changes_address_and_preserves_previous_address_in_event(): void
    {
        $p = $this->property();
        $p->releaseEvents();
        $new = $this->otherAddress();
        $p->changeAddress($new, $this->later());
        $event = $p->releaseEvents()[0];
        self::assertInstanceOf(AddressChanged::class, $event);
        self::assertTrue($event->previousAddress->equals($this->address()));
        self::assertTrue($event->newAddress->equals($new));
    }

    public function test_identical_address_is_rejected(): void
    {
        $p = $this->property();
        $this->expectException(PropertyViolation::class);
        $p->changeAddress($this->address(), $this->now());
    }

    public function test_same_physical_address_with_a_different_address_id_is_rejected(): void
    {
        $p = $this->property();
        $samePhysicalAddress = new Address(AddressId::fromString('50000000-0000-4000-8000-000000000099'), $this->address()->placeId, $this->address()->line);

        $this->expectException(PropertyViolation::class);
        $p->changeAddress($samePhysicalAddress, $this->later());
    }

    public function test_it_archives_once_and_emits_event(): void
    {
        $p = $this->property();
        $p->releaseEvents();
        $p->archive($this->later());
        self::assertSame(PropertyStatus::Archived, $p->status());
        self::assertInstanceOf(PropertyArchived::class, $p->releaseEvents()[0]);
    }

    public function test_archived_property_cannot_be_updated(): void
    {
        $p = $this->property();
        $p->archive($this->later());
        $this->expectException(PropertyViolation::class);
        $p->update(PropertyType::Villa, $this->surface(), $p->rooms(), $p->bathrooms(), null, $this->businessYear(), $this->policy(), $this->later());
    }

    public function test_archived_property_cannot_change_address(): void
    {
        $p = $this->property();
        $p->archive($this->later());
        $this->expectException(PropertyViolation::class);
        $p->changeAddress($this->otherAddress(), $this->later());
    }

    public function test_property_cannot_be_archived_twice(): void
    {
        $p = $this->property();
        $p->archive($this->later());
        $this->expectException(PropertyViolation::class);
        $p->archive($this->later());
    }

    public function test_past_change_is_rejected(): void
    {
        $p = $this->property();
        $this->expectException(InvalidPropertyValue::class);
        $p->archive(new DateTimeImmutable('2026-07-16T11:00:00+00:00'));
    }

    public function test_release_events_empties_collection(): void
    {
        $p = $this->property();
        self::assertNotEmpty($p->releaseEvents());
        self::assertSame([], $p->releaseEvents());
    }

    public function test_last_changed_at_returns_registration_date_without_altering_version_or_events(): void
    {
        $property = $this->property();

        self::assertEquals($this->now(), $property->lastChangedAt());
        self::assertSame(0, $property->version());
        self::assertCount(1, $property->releaseEvents());
    }

    public function test_last_changed_at_tracks_each_successful_mutation(): void
    {
        $property = $this->property();
        $property->update(PropertyType::Villa, SurfaceArea::fromSquareMeters(150), RoomCount::fromInt(6), BathroomCount::fromInt(3), ConstructionYear::fromInt(2020), $this->businessYear(), $this->policy(), $this->later());
        self::assertEquals($this->later(), $property->lastChangedAt());

        $addressDate = new DateTimeImmutable('2026-07-16T12:02:00+00:00');
        $property->changeAddress($this->otherAddress(), $addressDate);
        self::assertEquals($addressDate, $property->lastChangedAt());

        $archiveDate = new DateTimeImmutable('2026-07-16T12:03:00+00:00');
        $property->archive($archiveDate);
        self::assertEquals($archiveDate, $property->lastChangedAt());
        self::assertSame(3, $property->version());
    }

    public function test_antidated_mutation_remains_rejected_without_changing_last_changed_at(): void
    {
        $property = $this->property();
        $initial = $property->lastChangedAt();

        try {
            $property->archive(new DateTimeImmutable('2026-07-15T12:00:00+00:00'));
            self::fail('An antidated mutation must remain forbidden.');
        } catch (InvalidPropertyValue) {
            self::assertEquals($initial, $property->lastChangedAt());
            self::assertSame(0, $property->version());
        }
    }

    public function test_reconstitution_restores_exact_last_changed_at_without_events(): void
    {
        $date = new DateTimeImmutable('2026-07-16T12:04:05.123456+02:00');
        $property = Property::reconstitute($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2018), $this->address(), PropertyStatus::Archived, $date, 7);

        self::assertEquals($date, $property->lastChangedAt());
        self::assertSame(7, $property->version());
        self::assertSame([], $property->releaseEvents());
    }

    private function property(): Property
    {
        return Property::register($this->id(), $this->reference(), PropertyType::Apartment, $this->surface(), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2018), $this->address(), $this->businessYear(), $this->policy(), $this->now());
    }

    private function id(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function reference(): PropertyReference
    {
        return PropertyReference::fromString('prop-dkr-001');
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

    private function later(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:01:00+00:00');
    }

    private function businessYear(): BusinessYear
    {
        return BusinessYear::fromInt(2026);
    }

    private function policy(): PropertyTypePolicy
    {
        return new PropertyTypePolicy;
    }
}
