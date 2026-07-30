<?php

namespace Tests\Unit\Contracts\Media;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaCollectionViolation;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaStatus;
use PHPUnit\Framework\TestCase;

abstract class MediaCollectionRegistryContract extends TestCase
{
    private MediaCollectionRegistryHarness $harness;

    private MediaCollectionRegistry $registry;

    final protected function setUp(): void
    {
        $this->harness = $this->createHarness();
        $this->registry = $this->harness->freshRegistry();
    }

    abstract protected function createHarness(): MediaCollectionRegistryHarness;

    final public function test_unknown_identity_returns_explicit_absence(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
    }

    final public function test_add_then_find_restores_empty_collection_faithfully(): void
    {
        $expected = $this->harness->emptyCollection();
        $this->registry->add($expected);
        $actual = $this->requiredFind($expected);

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->propertyId()->value, $actual->propertyId()->value);
        self::assertEquals($expected->lastChangedAt(), $actual->lastChangedAt());
        self::assertSame(0, $actual->version());
        self::assertSame([], $actual->items());
    }

    final public function test_populated_reads_are_faithful_detached_and_independent(): void
    {
        $collection = $this->persistedWithTwo();
        $first = $this->requiredFind($collection);
        $second = $this->requiredFind($collection);
        $this->harness->mutate($first);

        self::assertNotSame($first, $second);
        self::assertEquals($collection->items(), $second->items());
        self::assertSame(2, $this->requiredFind($collection)->version());
    }

    final public function test_reloaded_collection_has_no_residual_or_replayed_events(): void
    {
        $collection = $this->persistedWithTwo();

        self::assertSame([], $this->requiredFind($collection)->releaseEvents());
        self::assertSame([], $this->requiredFind($collection)->releaseEvents());
    }

    final public function test_successful_reservation_and_save_preserve_caller_events(): void
    {
        $collection = $this->harness->emptyCollection();
        $this->registry->add($collection);
        $loaded = $this->requiredFind($collection);
        $this->harness->addMedia($loaded, $this->harness->firstMediaId(), 1);
        $this->registry->saveWithMediaReservation($loaded, $this->harness->firstMediaId(), 0);
        self::assertNotEmpty($loaded->releaseEvents());
        $loaded = $this->requiredFind($collection);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 1);
        self::assertNotEmpty($loaded->releaseEvents());
    }

    final public function test_duplicate_collection_identity_uses_public_conflict_without_overwrite(): void
    {
        $this->registry->add($this->harness->emptyCollection());

        try {
            $this->registry->add($this->harness->emptyCollection());
            self::fail('Duplicate collection identity must fail.');
        } catch (MediaCollectionIdConflict) {
            self::assertSame(0, $this->registry->find($this->harness->primaryId())?->version());
        }
    }

    final public function test_save_with_exact_version_succeeds_and_registry_never_invents_version(): void
    {
        $collection = $this->persistedWithTwo();
        $loaded = $this->requiredFind($collection);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 2);

        self::assertSame($loaded->version(), $this->requiredFind($collection)->version());
        self::assertSame(3, $this->requiredFind($collection)->version());
    }

    final public function test_stale_and_absent_saves_use_concurrency_conflict(): void
    {
        $collection = $this->persistedWithTwo();
        $winner = $this->requiredFind($collection);
        $stale = $this->requiredFind($collection);
        $this->harness->mutate($winner);
        $this->harness->mutate($stale);
        $this->registry->save($winner, 2);
        try {
            $this->registry->save($stale, 2);
            self::fail('Stale save must fail.');
        } catch (ConcurrentMediaCollectionModification) {
            $absent = $this->harness->emptyCollection($this->harness->distinctId());
            $this->harness->addMedia($absent, $this->harness->firstMediaId(), 1);
            $this->expectException(ConcurrentMediaCollectionModification::class);
            $this->registry->save($absent, 0);
        }
    }

    final public function test_media_identity_is_globally_reserved_and_conflict_is_distinct(): void
    {
        $this->persistedWithTwo();
        $other = $this->harness->emptyCollection($this->harness->distinctId());
        $this->registry->add($other);
        $this->harness->addMedia($other, $this->harness->firstMediaId(), 1);

        $this->expectException(MediaIdConflict::class);
        $this->registry->saveWithMediaReservation($other, $this->harness->firstMediaId(), 0);
    }

    final public function test_removed_or_archived_media_identity_remains_reserved_and_terminal(): void
    {
        $collection = $this->persistedWithTwo();
        $loaded = $this->requiredFind($collection);
        $this->harness->archiveSecond($loaded);
        $this->registry->save($loaded, 2);
        $archived = $this->requiredFind($collection);
        self::assertSame(MediaStatus::Archived, $archived->items()[1]->status);
        try {
            $archived->changeCaption($this->harness->secondMediaId(), MediaCaption::fromString('Forbidden terminal mutation'), $archived->lastChangedAt());
            self::fail('Archived MediaItem must remain terminal.');
        } catch (MediaCollectionViolation) {
            $other = $this->harness->emptyCollection($this->harness->distinctId());
            $this->registry->add($other);
            $this->harness->addMedia($other, $this->harness->secondMediaId(), 1);
            $this->expectException(MediaIdConflict::class);
            $this->registry->saveWithMediaReservation($other, $this->harness->secondMediaId(), 0);
        }
    }

    final public function test_failed_reserved_save_rolls_back_mutation_and_reservation_and_keeps_events(): void
    {
        $collection = $this->harness->emptyCollection();
        $this->registry->add($collection);
        $loaded = $this->requiredFind($collection);
        $this->harness->addMedia($loaded, $this->harness->firstMediaId(), 1);
        $this->harness->failNextWrite($this->registry);
        try {
            $this->registry->saveWithMediaReservation($loaded, $this->harness->firstMediaId(), 0);
            self::fail('Controlled failure must be visible.');
        } catch (ConcurrentMediaCollectionModification) {
            self::assertSame([], $this->requiredFind($collection)->items());
            self::assertNotEmpty($loaded->releaseEvents());
            $other = $this->harness->emptyCollection($this->harness->distinctId());
            $this->registry->add($other);
            $this->harness->addMedia($other, $this->harness->firstMediaId(), 1);
            $this->registry->saveWithMediaReservation($other, $this->harness->firstMediaId(), 0);
            self::assertCount(1, $this->requiredFind($other)->items());
        }
    }

    final public function test_failed_plain_save_rolls_back_mutation_and_keeps_events(): void
    {
        $collection = $this->persistedWithTwo();
        $loaded = $this->requiredFind($collection);
        $this->harness->mutate($loaded);
        $this->harness->failNextWrite($this->registry);

        try {
            $this->registry->save($loaded, 2);
            self::fail('Controlled plain save failure must be visible.');
        } catch (ConcurrentMediaCollectionModification) {
            self::assertSame(2, $this->requiredFind($collection)->version());
            self::assertNotSame('Updated contract caption', $this->requiredFind($collection)->items()[0]->caption?->value);
            self::assertNotEmpty($loaded->releaseEvents());
        }
    }

    final public function test_item_order_primary_and_complete_values_survive_reconstruction(): void
    {
        $collection = $this->persistedWithTwo();
        $items = $this->requiredFind($collection)->items();

        self::assertCount(2, $items);
        self::assertSame([$this->harness->firstMediaId()->value, $this->harness->secondMediaId()->value], array_map(static fn ($item): string => $item->id->value, $items));
        self::assertSame([1, 2], array_map(static fn ($item): int => $item->order->value, $items));
        self::assertTrue($items[0]->primary);
        self::assertFalse($items[1]->primary);
        self::assertSame($collection->id()->value, $items[0]->collectionId->value);
    }

    final public function test_each_scenario_starts_with_an_isolated_empty_registry(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
        self::assertNull($this->registry->find($this->harness->distinctId()));
    }

    private function persistedWithTwo(): MediaCollection
    {
        $collection = $this->harness->emptyCollection();
        $this->registry->add($collection);
        $loaded = $this->requiredFind($collection);
        $this->harness->addMedia($loaded, $this->harness->firstMediaId(), 1);
        $this->registry->saveWithMediaReservation($loaded, $this->harness->firstMediaId(), 0);
        $loaded = $this->requiredFind($collection);
        $this->harness->addMedia($loaded, $this->harness->secondMediaId(), 2);
        $this->registry->saveWithMediaReservation($loaded, $this->harness->secondMediaId(), 1);

        return $this->requiredFind($collection);
    }

    private function requiredFind(MediaCollection $collection): MediaCollection
    {
        $loaded = $this->registry->find($collection->id());
        self::assertNotNull($loaded);

        return $loaded;
    }
}
