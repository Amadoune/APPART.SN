<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PersistentPlaceIntegrity;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceSnapshot;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PlaceMapperTest extends TestCase
{
    public function test_complete_parent_coordinates_aliases_and_version_round_trip(): void
    {
        $parent = $this->place('10000000-0000-4000-8000-000000000001', 'Senegal', 'SN', PlaceType::Country);
        $place = Place::create($this->id('10000000-0000-4000-8000-000000000002'), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $parent, Coordinates::fromDecimal(14.7167, -17.4677));
        $place->rename(PlaceName::fromString('Région de Dakar'), $this->at('+1 second'));

        $copy = (new PlaceMapper)->toAggregate((new PlaceMapper)->toSnapshot($place));

        self::assertSame($place->id()->value, $copy->id()->value);
        self::assertSame('Région de Dakar', $copy->officialName()->value);
        self::assertSame($parent->id()->value, $copy->parent()?->placeId->value);
        self::assertSame(14.7167, $copy->coordinates()?->latitude);
        self::assertCount(1, $copy->aliases());
        self::assertSame(2, $copy->version());
        self::assertTrue($copy->isEnabled());
    }

    public function test_null_coordinates_disabled_and_merged_are_reconstructed(): void
    {
        $target = $this->place('10000000-0000-4000-8000-000000000003', 'Sénégal', 'SN2', PlaceType::Country);
        $source = $this->place('10000000-0000-4000-8000-000000000004', 'Senegal ancien', 'SN3', PlaceType::Country);
        $source->mergeInto($target, $this->at('+1 second'));
        $copy = (new PlaceMapper)->toAggregate((new PlaceMapper)->toSnapshot($source));

        self::assertNull($copy->coordinates());
        self::assertFalse($copy->isEnabled());
        self::assertSame($target->id()->value, $copy->mergedInto()?->value);
    }

    public function test_corrupted_snapshot_is_rejected(): void
    {
        $this->expectException(PersistentPlaceIntegrity::class);
        (new PlaceMapper)->toAggregate(new PlaceSnapshot('bad', 'Dakar', 'DKR', 'city', 'SN', null, null, null, null, [], true, null, 1));
    }

    private function place(string $id, string $name, string $code, PlaceType $type): Place
    {
        return Place::create($this->id($id), PlaceName::fromString($name), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at());
    }

    private function id(string $value): PlaceId
    {
        return PlaceId::fromString($value);
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-13T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}
