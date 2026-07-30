<?php

namespace Tests\Unit\Modules\Media;

use Appart\Modules\Media\Domain\Event\MediaAdded;
use Appart\Modules\Media\Domain\Event\MediaArchived;
use Appart\Modules\Media\Domain\Event\MediaCaptionChanged;
use Appart\Modules\Media\Domain\Event\MediaMarkedPrimary;
use Appart\Modules\Media\Domain\Event\MediaRemoved;
use Appart\Modules\Media\Domain\Event\MediaReordered;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\Exception\MediaCollectionViolation;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\Model\MediaItem;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaStatus;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MediaCollectionTest extends TestCase
{
    public function test_collection_has_stable_property_ownership(): void
    {
        $c = $this->collection();
        self::assertSame($this->collectionId()->value, $c->id()->value);
        self::assertSame($this->propertyId()->value, $c->propertyId()->value);
    }

    public function test_first_media_becomes_primary_and_emits_ordered_events(): void
    {
        $c = $this->collection();
        $this->addFirst($c);
        $events = $c->releaseEvents();
        self::assertTrue($c->items()[0]->primary);
        self::assertInstanceOf(MediaAdded::class, $events[0]);
        self::assertTrue($events[0]->primary);
        self::assertInstanceOf(MediaMarkedPrimary::class, $events[1]);
    }

    public function test_later_media_does_not_replace_primary(): void
    {
        $c = $this->withTwo();
        self::assertSame($this->firstId()->value, $c->primary()?->id->value);
        self::assertFalse($c->items()[1]->primary);
    }

    public function test_duplicate_checksum_is_rejected_for_all_history(): void
    {
        $c = $this->withTwo();
        $c->archive($this->secondId(), null, $this->later());
        $this->expectException(MediaCollectionViolation::class);
        $c->add($this->thirdId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(2), null, MediaSource::Owner, $this->later());
    }

    public function test_duplicate_active_order_is_rejected(): void
    {
        $c = $this->collection();
        $this->addFirst($c);
        $this->expectException(MediaCollectionViolation::class);
        $c->add($this->secondId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
    }

    public function test_duplicate_media_identity_is_rejected(): void
    {
        $c = $this->collection();
        $this->addFirst($c);
        $this->expectException(MediaCollectionViolation::class);
        $c->add($this->firstId(), MediaType::Image, $this->checksum(3), MediaOrder::fromInt(2), null, MediaSource::Owner, $this->now());
    }

    public function test_it_marks_exactly_one_active_media_primary(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $c->markPrimary($this->secondId(), $this->later());
        $event = $c->releaseEvents()[0];
        self::assertSame($this->secondId()->value, $c->primary()?->id->value);
        self::assertCount(1, array_filter($c->items(), fn ($item) => $item->primary));
        self::assertInstanceOf(MediaMarkedPrimary::class, $event);
        self::assertSame($this->firstId()->value, $event->previousPrimaryId?->value);
    }

    public function test_primary_cannot_be_removed_without_replacement(): void
    {
        $c = $this->withTwo();
        $this->expectException(MediaCollectionViolation::class);
        $c->remove($this->firstId(), null, $this->later());
    }

    public function test_primary_removal_atomically_replaces_primary_and_keeps_history(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $c->remove($this->firstId(), $this->secondId(), $this->later());
        $events = $c->releaseEvents();
        self::assertSame(MediaStatus::Removed, $c->items()[0]->status);
        self::assertSame($this->secondId()->value, $c->primary()?->id->value);
        self::assertInstanceOf(MediaMarkedPrimary::class, $events[0]);
        self::assertInstanceOf(MediaRemoved::class, $events[1]);
    }

    public function test_primary_archival_also_requires_and_applies_replacement(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $c->archive($this->firstId(), $this->secondId(), $this->later());
        $events = $c->releaseEvents();
        self::assertSame(MediaStatus::Archived, $c->items()[0]->status);
        self::assertInstanceOf(MediaMarkedPrimary::class, $events[0]);
        self::assertInstanceOf(MediaArchived::class, $events[1]);
    }

    public function test_archived_media_cannot_be_modified(): void
    {
        $c = $this->withTwo();
        $c->archive($this->secondId(), null, $this->later());
        $this->expectException(MediaCollectionViolation::class);
        $c->changeCaption($this->secondId(), MediaCaption::fromString('Nouvelle légende'), $this->later());
    }

    public function test_it_reorders_every_active_media_atomically(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $c->reorder([$this->secondId(), $this->firstId()], $this->later());
        $event = $c->releaseEvents()[0];
        self::assertSame(2, $c->items()[0]->order->value);
        self::assertSame(1, $c->items()[1]->order->value);
        self::assertInstanceOf(MediaReordered::class, $event);
        self::assertSame(1, $event->orders[$this->secondId()->value]);
    }

    public function test_partial_or_duplicate_reordering_is_rejected(): void
    {
        $c = $this->withTwo();
        $this->expectException(MediaCollectionViolation::class);
        $c->reorder([$this->firstId(), $this->firstId()], $this->later());
    }

    public function test_failed_reordering_is_fully_atomic(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $version = $c->version();
        $orders = array_map(static fn (MediaItem $item): int => $item->order->value, $c->items());

        try {
            $c->reorder([$this->secondId(), $this->secondId()], $this->later());
            self::fail('An invalid reordering must be rejected.');
        } catch (MediaCollectionViolation) {
            self::assertSame($orders, array_map(static fn (MediaItem $item): int => $item->order->value, $c->items()));
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_primary_cannot_be_replaced_by_inactive_media(): void
    {
        $c = $this->withTwo();
        $c->archive($this->secondId(), null, $this->later());
        $c->releaseEvents();
        $version = $c->version();

        try {
            $c->remove($this->firstId(), $this->secondId(), $this->later());
            self::fail('An inactive media cannot become primary.');
        } catch (MediaCollectionViolation) {
            self::assertSame($this->firstId()->value, $c->primary()?->id->value);
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_primary_cannot_be_archived_without_replacement(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $version = $c->version();

        try {
            $c->archive($this->firstId(), null, $this->later());
            self::fail('A primary media requires an explicit replacement.');
        } catch (MediaCollectionViolation) {
            self::assertSame(MediaStatus::Active, $c->items()[0]->status);
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_removed_and_archived_media_are_terminal(): void
    {
        $removedCollection = $this->withTwo();
        $removedCollection->remove($this->secondId(), null, $this->later());
        $removed = $removedCollection->items()[1];

        try {
            $removed->remove($this->later());
            self::fail('A removed media cannot be removed twice.');
        } catch (MediaCollectionViolation) {
            self::assertSame(MediaStatus::Removed, $removed->status);
        }
        try {
            $removed->archive($this->later());
            self::fail('A removed media cannot be archived.');
        } catch (MediaCollectionViolation) {
            self::assertSame(MediaStatus::Removed, $removed->status);
        }

        $archivedCollection = $this->withTwo();
        $archivedCollection->archive($this->secondId(), null, $this->later());
        $archived = $archivedCollection->items()[1];

        try {
            $archived->archive($this->later());
            self::fail('An archived media cannot be archived twice.');
        } catch (MediaCollectionViolation) {
            self::assertSame(MediaStatus::Archived, $archived->status);
        }
        try {
            $archived->remove($this->later());
            self::fail('An archived media cannot be removed.');
        } catch (MediaCollectionViolation) {
            self::assertSame(MediaStatus::Archived, $archived->status);
        }
    }

    public function test_media_item_rejects_impossible_states_and_chronology(): void
    {
        $base = [$this->firstId(), $this->collectionId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner];
        $addedAt = $this->now();
        $before = new DateTimeImmutable('2026-07-16T11:59:00+00:00');

        $invalidStates = [
            [...$base, MediaStatus::Archived, true, $addedAt, null, $this->later()],
            [...$base, MediaStatus::Removed, false, $addedAt, null, null],
            [...$base, MediaStatus::Active, false, $addedAt, $this->later(), null],
            [...$base, MediaStatus::Removed, false, $addedAt, $this->later(), $this->later()],
            [...$base, MediaStatus::Archived, false, $addedAt, null, $before],
        ];

        foreach ($invalidStates as $arguments) {
            try {
                new MediaItem(...$arguments);
                self::fail('An impossible media state must be rejected.');
            } catch (InvalidMediaValue) {
                self::assertTrue(true);
            }
        }
    }

    public function test_unchanged_reordering_is_rejected(): void
    {
        $c = $this->withTwo();
        $this->expectException(MediaCollectionViolation::class);
        $c->reorder([$this->firstId(), $this->secondId()], $this->later());
    }

    public function test_caption_change_is_historicized_by_event(): void
    {
        $c = $this->withTwo();
        $c->releaseEvents();
        $caption = MediaCaption::fromString('Vue principale du bien');
        $c->changeCaption($this->secondId(), $caption, $this->later());
        $event = $c->releaseEvents()[0];
        self::assertSame($caption->value, $c->items()[1]->caption?->value);
        self::assertInstanceOf(MediaCaptionChanged::class, $event);
        self::assertNull($event->previousCaption);
    }

    public function test_past_operation_is_rejected(): void
    {
        $c = $this->collection();
        $this->expectException(InvalidMediaValue::class);
        $c->add($this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, new DateTimeImmutable('2026-07-16T11:00:00+00:00'));
    }

    public function test_release_events_empties_collection(): void
    {
        $c = $this->collection();
        $this->addFirst($c);
        self::assertNotEmpty($c->releaseEvents());
        self::assertSame([], $c->releaseEvents());
    }

    public function test_last_changed_at_starts_at_creation_and_reading_it_changes_nothing(): void
    {
        $collection = $this->collection();
        $items = $collection->items();

        self::assertEquals($this->now(), $collection->lastChangedAt());
        self::assertSame(0, $collection->version());
        self::assertSame($items, $collection->items());
        self::assertSame([], $collection->releaseEvents());
    }

    public function test_last_changed_at_tracks_add_reorder_primary_and_caption_exactly(): void
    {
        $collection = $this->collection();
        $addFirstAt = new DateTimeImmutable('2026-07-16T12:01:00.100000+00:00');
        $collection->add($this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $addFirstAt);
        self::assertEquals($addFirstAt, $collection->lastChangedAt());
        $addSecondAt = new DateTimeImmutable('2026-07-16T12:02:00.200000+00:00');
        $collection->add($this->secondId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(2), null, MediaSource::Professional, $addSecondAt);

        $reorderAt = new DateTimeImmutable('2026-07-16T12:03:00.300000+00:00');
        $collection->reorder([$this->secondId(), $this->firstId()], $reorderAt);
        self::assertEquals($reorderAt, $collection->lastChangedAt());
        $primaryAt = new DateTimeImmutable('2026-07-16T12:04:00.400000+00:00');
        $collection->markPrimary($this->secondId(), $primaryAt);
        self::assertEquals($primaryAt, $collection->lastChangedAt());
        $captionAt = new DateTimeImmutable('2026-07-16T12:05:00.500000+00:00');
        $collection->changeCaption($this->firstId(), MediaCaption::fromString('Date exacte de la légende'), $captionAt);
        self::assertEquals($captionAt, $collection->lastChangedAt());
    }

    public function test_last_changed_at_tracks_remove_and_archive_exactly(): void
    {
        $removed = $this->withTwo();
        $removeAt = new DateTimeImmutable('2026-07-16T12:02:00.123456+00:00');
        $removed->remove($this->secondId(), null, $removeAt);
        self::assertEquals($removeAt, $removed->lastChangedAt());

        $archived = $this->withTwo();
        $archiveAt = new DateTimeImmutable('2026-07-16T12:02:00.654321+00:00');
        $archived->archive($this->secondId(), null, $archiveAt);
        self::assertEquals($archiveAt, $archived->lastChangedAt());
    }

    public function test_antidated_mutation_stays_rejected_without_changing_date(): void
    {
        $collection = $this->withTwo();
        $initial = $collection->lastChangedAt();

        try {
            $collection->changeCaption($this->secondId(), MediaCaption::fromString('Mutation antidatée'), new DateTimeImmutable('2026-07-15T12:00:00+00:00'));
            self::fail('An antidated mutation must remain forbidden.');
        } catch (InvalidMediaValue) {
            self::assertEquals($initial, $collection->lastChangedAt());
            self::assertSame(2, $collection->version());
        }
    }

    public function test_reconstitution_restores_exact_date_for_populated_and_empty_collections_without_events(): void
    {
        $date = new DateTimeImmutable('2026-07-16T12:06:07.123456+02:00');
        $populated = MediaCollection::reconstitute($this->collectionId(), $this->propertyId(), $date, $this->withTwo()->items(), 8);
        self::assertEquals($date, $populated->lastChangedAt());
        self::assertSame(8, $populated->version());
        self::assertSame([], $populated->releaseEvents());

        $empty = MediaCollection::reconstitute($this->collectionId(), $this->propertyId(), $date, [], 0);
        self::assertEquals($date, $empty->lastChangedAt());
        self::assertSame([], $empty->items());
        self::assertSame([], $empty->releaseEvents());
    }

    private function collection(): MediaCollection
    {
        return MediaCollection::create($this->collectionId(), $this->propertyId(), $this->now());
    }

    private function withTwo(): MediaCollection
    {
        $c = $this->collection();
        $this->addFirst($c);
        $c->add($this->secondId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(2), null, MediaSource::Professional, $this->now());

        return $c;
    }

    private function addFirst(MediaCollection $c): void
    {
        $c->add($this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), MediaCaption::fromString('Façade principale'), MediaSource::Owner, $this->now());
    }

    private function collectionId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('60000000-0000-4000-8000-000000000001');
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function firstId(): MediaId
    {
        return MediaId::fromString('60000000-0000-4000-8000-000000000002');
    }

    private function secondId(): MediaId
    {
        return MediaId::fromString('60000000-0000-4000-8000-000000000003');
    }

    private function thirdId(): MediaId
    {
        return MediaId::fromString('60000000-0000-4000-8000-000000000004');
    }

    private function checksum(int $digit): MediaChecksum
    {
        return MediaChecksum::fromSha256(str_repeat((string) $digit, 64));
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }

    private function later(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:01:00+00:00');
    }
}
