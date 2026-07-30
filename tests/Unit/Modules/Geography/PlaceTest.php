<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Domain\Event\PlaceCreated;
use Appart\Modules\Geography\Domain\Event\PlaceDisabled;
use Appart\Modules\Geography\Domain\Event\PlaceEnabled;
use Appart\Modules\Geography\Domain\Event\PlaceMerged;
use Appart\Modules\Geography\Domain\Event\PlaceRenamed;
use Appart\Modules\Geography\Domain\Exception\CannotMergePlaceIntoItself;
use Appart\Modules\Geography\Domain\Exception\InvalidMergeTarget;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceHierarchy;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyDisabled;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyEnabled;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyMerged;
use Appart\Modules\Geography\Domain\Exception\PlaceNameUnchanged;
use Appart\Modules\Geography\Domain\Model\AdministrativeDivision;
use Appart\Modules\Geography\Domain\Model\GeographicAlias;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PlaceTest extends TestCase
{
    public function test_it_creates_an_enabled_place_with_an_official_identity(): void
    {
        $place = $this->city();

        self::assertSame('Dakar', $place->officialName()->value);
        self::assertSame('DKR', $place->code()->value);
        self::assertSame(PlaceType::City, $place->type());
        self::assertSame('SN', $place->countryCode()->value);
        self::assertTrue($place->isEnabled());
        self::assertSame(14.7167, $place->coordinates()?->latitude);
        $event = $place->releaseEvents()[0];
        self::assertInstanceOf(PlaceCreated::class, $event);
        self::assertSame('DKR', $event->officialCode->value);
        self::assertSame('SN', $event->countryCode->value);
        self::assertSame($place->parent()?->placeId->value, $event->parentPlaceId?->value);
        self::assertSame($place->coordinates(), $event->coordinates);
        self::assertSame(1, $place->version());
        self::assertSame(1, $event->aggregateVersion());
    }

    public function test_it_reconstitutes_complete_state_without_replaying_events(): void
    {
        $place = Place::reconstitute(
            PlaceId::fromString('00000000-0000-4000-8000-000000000003'),
            PlaceName::fromString('Ville de Dakar'),
            PlaceCode::fromString('DKR'),
            PlaceType::City,
            CountryCode::fromString('SN'),
            new AdministrativeDivision($this->region()->id(), PlaceType::Region),
            Coordinates::fromDecimal(14.7167, -17.4677),
            [new GeographicAlias(PlaceName::fromString('Dakar'), $this->when())],
            false,
            null,
            7,
        );

        self::assertSame(7, $place->version());
        self::assertFalse($place->isEnabled());
        self::assertSame('Dakar', $place->aliases()[0]->name->value);
        self::assertSame([], $place->releaseEvents());
    }

    public function test_it_renames_a_place_and_preserves_the_previous_name_as_an_alias(): void
    {
        $place = $this->city();
        $place->releaseEvents();

        $place->rename(PlaceName::fromString('Ville de Dakar'), $this->when());
        $events = $place->releaseEvents();

        self::assertSame('Ville de Dakar', $place->officialName()->value);
        self::assertSame('Dakar', $place->aliases()[0]->name->value);
        self::assertCount(1, $place->aliases());
        self::assertInstanceOf(PlaceRenamed::class, $events[0]);
    }

    public function test_aliases_remain_unique_across_successive_renames(): void
    {
        $place = $this->city();

        $place->rename(PlaceName::fromString('Ville de Dakar'), $this->when());
        $place->rename(PlaceName::fromString('Dakar'), $this->when());
        $place->rename(PlaceName::fromString('Ville de Dakar'), $this->when());

        self::assertCount(1, $place->aliases());
        self::assertSame('Dakar', $place->aliases()[0]->name->value);
    }

    public function test_it_rejects_a_rename_to_the_current_name(): void
    {
        $place = $this->city();

        $this->expectException(PlaceNameUnchanged::class);

        $place->rename(PlaceName::fromString(' dakar '), $this->when());
    }

    public function test_it_disables_a_place(): void
    {
        $place = $this->city();
        $place->releaseEvents();

        $place->disable($this->when());

        self::assertFalse($place->isEnabled());
        self::assertInstanceOf(PlaceDisabled::class, $place->releaseEvents()[0]);
    }

    public function test_it_rejects_disabling_an_already_disabled_place(): void
    {
        $place = $this->city();
        $place->disable($this->when());

        $this->expectException(PlaceAlreadyDisabled::class);

        $place->disable($this->when());
    }

    public function test_it_enables_a_disabled_place(): void
    {
        $place = $this->city();
        $place->disable($this->when());
        $place->releaseEvents();

        $place->enable($this->when());

        self::assertTrue($place->isEnabled());
        self::assertInstanceOf(PlaceEnabled::class, $place->releaseEvents()[0]);
    }

    public function test_it_rejects_enabling_an_active_place(): void
    {
        $place = $this->city();

        $this->expectException(PlaceAlreadyEnabled::class);

        $place->enable($this->when());
    }

    public function test_it_merges_a_place_into_an_active_peer(): void
    {
        $source = $this->city();
        $target = $this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');
        $source->releaseEvents();

        $source->mergeInto($target, $this->when());
        $events = $source->releaseEvents();

        self::assertFalse($source->isEnabled());
        self::assertTrue($source->mergedInto()?->equals($target->id()));
        self::assertInstanceOf(PlaceMerged::class, $events[0]);
    }

    public function test_it_rejects_merging_a_place_into_itself(): void
    {
        $place = $this->city();

        $this->expectException(CannotMergePlaceIntoItself::class);

        $place->mergeInto($place, $this->when());
    }

    public function test_it_rejects_a_merge_into_an_inactive_target(): void
    {
        $source = $this->city();
        $target = $this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');
        $target->disable($this->when());

        $this->expectException(InvalidMergeTarget::class);

        $source->mergeInto($target, $this->when());
    }

    public function test_it_rejects_a_merge_between_different_place_types(): void
    {
        $source = $this->city();
        $target = Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000004'),
            PlaceName::fromString('Dakar Plateau'),
            PlaceCode::fromString('DKR-PLT'),
            PlaceType::District,
            CountryCode::fromString('SN'),
            $this->when(),
            $source,
        );

        $this->expectException(InvalidMergeTarget::class);

        $source->mergeInto($target, $this->when());
    }

    public function test_it_rejects_a_merge_between_different_countries(): void
    {
        $source = $this->city();
        $target = Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000004'),
            PlaceName::fromString('Banjul'),
            PlaceCode::fromString('BJL'),
            PlaceType::City,
            CountryCode::fromString('GM'),
            $this->when(),
            $this->region(
                '00000000-0000-4000-8000-000000000005',
                'Banjul',
                'BJL-REG',
                $this->country('00000000-0000-4000-8000-000000000006', 'Gambie', 'GM', 'GM'),
            ),
        );

        $this->expectException(InvalidMergeTarget::class);

        $source->mergeInto($target, $this->when());
    }

    public function test_a_merged_place_cannot_be_merged_again(): void
    {
        $source = $this->city();
        $firstTarget = $this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');
        $secondTarget = $this->city('00000000-0000-4000-8000-000000000005', 'Dakar Métropole', 'DMK');
        $source->mergeInto($firstTarget, $this->when());

        $this->expectException(PlaceAlreadyMerged::class);

        $source->mergeInto($secondTarget, $this->when());
    }

    public function test_a_merged_place_cannot_be_renamed(): void
    {
        $source = $this->city();
        $source->mergeInto($this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK'), $this->when());

        $this->expectException(PlaceAlreadyMerged::class);

        $source->rename(PlaceName::fromString('Nouveau nom'), $this->when());
    }

    public function test_a_merged_place_cannot_be_disabled_again(): void
    {
        $source = $this->city();
        $source->mergeInto($this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK'), $this->when());

        $this->expectException(PlaceAlreadyMerged::class);

        $source->disable($this->when());
    }

    public function test_an_explicitly_disabled_source_can_be_merged_for_consolidation(): void
    {
        $source = $this->city();
        $target = $this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');
        $source->disable($this->when());

        $source->mergeInto($target, $this->when());

        self::assertTrue($source->mergedInto()?->equals($target->id()));
    }

    public function test_releasing_events_preserves_order_and_empties_the_internal_collection(): void
    {
        $place = $this->city();
        $place->rename(PlaceName::fromString('Ville de Dakar'), $this->when());

        $events = $place->releaseEvents();

        self::assertInstanceOf(PlaceCreated::class, $events[0]);
        self::assertInstanceOf(PlaceRenamed::class, $events[1]);
        self::assertEquals($this->when(), $events[1]->occurredAt());
        self::assertSame([], $place->releaseEvents());
    }

    public function test_a_merged_place_cannot_be_reenabled(): void
    {
        $source = $this->city();
        $target = $this->city('00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');
        $source->mergeInto($target, $this->when());

        $this->expectException(PlaceAlreadyMerged::class);

        $source->enable($this->when());
    }

    public function test_a_country_cannot_have_a_parent(): void
    {
        $this->expectException(InvalidPlaceHierarchy::class);

        Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000010'),
            PlaceName::fromString('Sénégal'),
            PlaceCode::fromString('SN'),
            PlaceType::Country,
            CountryCode::fromString('SN'),
            $this->when(),
            $this->region(),
        );
    }

    public function test_a_non_country_place_requires_a_parent(): void
    {
        $this->expectException(InvalidPlaceHierarchy::class);

        Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000010'),
            PlaceName::fromString('Dakar'),
            PlaceCode::fromString('DKR'),
            PlaceType::City,
            CountryCode::fromString('SN'),
            $this->when(),
        );
    }

    public function test_a_place_rejects_an_incompatible_parent_type(): void
    {
        $this->expectException(InvalidPlaceHierarchy::class);

        Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000010'),
            PlaceName::fromString('Dakar'),
            PlaceCode::fromString('DKR'),
            PlaceType::City,
            CountryCode::fromString('SN'),
            $this->when(),
            $this->country(),
        );
    }

    public function test_a_place_rejects_a_disabled_parent(): void
    {
        $parent = $this->region();
        $parent->disable($this->when());

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->newCityWithParent($parent);
    }

    public function test_a_place_rejects_a_merged_parent(): void
    {
        $parent = $this->region();
        $target = $this->region('00000000-0000-4000-8000-000000000005', 'Thiès', 'THR');
        $parent->mergeInto($target, $this->when());

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->newCityWithParent($parent);
    }

    public function test_a_place_rejects_a_parent_from_another_country(): void
    {
        $parent = $this->region(
            '00000000-0000-4000-8000-000000000005',
            'Banjul',
            'BJL',
            $this->country('00000000-0000-4000-8000-000000000006', 'Gambie', 'GM', 'GM'),
        );

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->newCityWithParent($parent);
    }

    private function city(
        string $id = '00000000-0000-4000-8000-000000000003',
        string $name = 'Dakar',
        string $code = 'DKR',
    ): Place {
        return Place::create(
            PlaceId::fromString($id),
            PlaceName::fromString($name),
            PlaceCode::fromString($code),
            PlaceType::City,
            CountryCode::fromString('SN'),
            $this->when(),
            $this->region(),
            Coordinates::fromDecimal(14.7167, -17.4677),
        );
    }

    private function newCityWithParent(Place $parent): Place
    {
        return Place::create(
            PlaceId::fromString('00000000-0000-4000-8000-000000000010'),
            PlaceName::fromString('Nouvelle ville'),
            PlaceCode::fromString('NEW'),
            PlaceType::City,
            CountryCode::fromString('SN'),
            $this->when(),
            $parent,
        );
    }

    private function region(
        string $id = '00000000-0000-4000-8000-000000000002',
        string $name = 'Dakar',
        string $code = 'DKR-REG',
        ?Place $country = null,
    ): Place {
        return Place::create(
            PlaceId::fromString($id),
            PlaceName::fromString($name),
            PlaceCode::fromString($code),
            PlaceType::Region,
            $country?->countryCode() ?? CountryCode::fromString('SN'),
            $this->when(),
            $country ?? $this->country(),
        );
    }

    private function country(
        string $id = '00000000-0000-4000-8000-000000000001',
        string $name = 'Sénégal',
        string $code = 'SN',
        string $countryCode = 'SN',
    ): Place {
        return Place::create(
            PlaceId::fromString($id),
            PlaceName::fromString($name),
            PlaceCode::fromString($code),
            PlaceType::Country,
            CountryCode::fromString($countryCode),
            $this->when(),
        );
    }

    private function when(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }
}
