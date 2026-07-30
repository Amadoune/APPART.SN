<?php

namespace Tests\Unit\Modules\Media;

use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionSnapshot;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemSnapshot;
use Appart\Modules\Media\Infrastructure\Persistence\PersistentMediaCollectionIntegrity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\Media\FakeMediaCollectionRegistryHarness;

final class MediaCollectionMapperTest extends TestCase
{
    public function test_empty_collection_round_trips_exactly_without_events(): void
    {
        $mapper = new MediaCollectionMapper;
        $expected = (new FakeMediaCollectionRegistryHarness)->emptyCollection();
        $actual = $mapper->toAggregate($mapper->toSnapshot($expected));
        self::assertEquals($expected, $actual);
        self::assertSame([], $actual->releaseEvents());
    }

    public function test_complete_items_round_trip_exactly(): void
    {
        $h = new FakeMediaCollectionRegistryHarness;
        $mapper = new MediaCollectionMapper;
        $expected = $h->emptyCollection();
        $h->addMedia($expected, $h->firstMediaId(), 1);
        $h->addMedia($expected, $h->secondMediaId(), 2);
        $expected->releaseEvents();
        $actual = $mapper->toAggregate($mapper->toSnapshot($expected));
        self::assertEquals($expected->items(), $actual->items());
        self::assertSame($expected->version(), $actual->version());
        self::assertEquals($expected->lastChangedAt(), $actual->lastChangedAt());
        self::assertSame([], $actual->releaseEvents());
    }

    #[DataProvider('invalidSnapshots')]
    public function test_invalid_snapshots_are_rejected(MediaCollectionSnapshot $snapshot): void
    {
        $this->expectException(PersistentMediaCollectionIntegrity::class);
        (new MediaCollectionMapper)->toAggregate($snapshot);
    }

    public static function invalidSnapshots(): array
    {
        $id = '34000000-0000-4000-8000-000000000001';
        $property = '33000000-0000-4000-8000-000000000001';
        $date = '2026-07-18T10:00:00.000000+00:00';
        $item = fn (string $media = '34000000-0000-4000-8000-000000000101', int $order = 1, bool $primary = true, string $status = 'active', ?string $checksum = null) => new MediaItemSnapshot($media, $id, 'image', $checksum ?? str_repeat('1', 64), $order, null, 'owner', $status, $primary, $date, null, null);

        return [
            'negative version' => [new MediaCollectionSnapshot($id, $property, $date, -1, [])],
            'invalid media id' => [new MediaCollectionSnapshot($id, $property, $date, 1, [new MediaItemSnapshot('invalid', $id, 'image', str_repeat('1', 64), 1, null, 'owner', 'active', true, $date, null, null)])],
            'invalid checksum' => [new MediaCollectionSnapshot($id, $property, $date, 1, [$item(checksum: 'invalid')])],
            'duplicate order' => [new MediaCollectionSnapshot($id, $property, $date, 2, [$item(), $item('34000000-0000-4000-8000-000000000102', 1, false, checksum: str_repeat('2', 64))])],
            'two primaries' => [new MediaCollectionSnapshot($id, $property, $date, 2, [$item(), $item('34000000-0000-4000-8000-000000000102', 2, true, checksum: str_repeat('2', 64))])],
            'unknown status' => [new MediaCollectionSnapshot($id, $property, $date, 1, [$item(status: 'unknown')])],
        ];
    }
}
