<?php

namespace Tests\Unit\Modules\Media;

use Appart\Modules\Media\Application\UseCase\AddMedia;
use Appart\Modules\Media\Application\UseCase\ArchiveMedia;
use Appart\Modules\Media\Application\UseCase\ChangeCaption;
use Appart\Modules\Media\Application\UseCase\CreateMediaCollection;
use Appart\Modules\Media\Application\UseCase\MarkPrimaryMedia;
use Appart\Modules\Media\Application\UseCase\RemoveMedia;
use Appart\Modules\Media\Application\UseCase\ReorderMedia;
use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaCollectionNotFound;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Exception\PropertyUnavailable;
use Appart\Modules\Media\Domain\Model\MediaCollection;
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
use Tests\Unit\Modules\Media\Support\FakeMediaCollectionRegistry;
use Tests\Unit\Modules\Media\Support\FakePropertyCatalog;

final class MediaUseCasesTest extends TestCase
{
    public function test_all_use_cases_orchestrate_the_collection_lifecycle(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        (new AddMedia($r))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
        (new AddMedia($r))->execute($this->collectionId(), $this->secondId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(2), null, MediaSource::Professional, $this->now());
        (new ChangeCaption($r))->execute($this->collectionId(), $this->secondId(), MediaCaption::fromString('Vue intérieure'), $this->now());
        (new MarkPrimaryMedia($r))->execute($this->collectionId(), $this->secondId(), $this->now());
        (new ReorderMedia($r))->execute($this->collectionId(), [$this->secondId(), $this->firstId()], $this->now());
        (new ArchiveMedia($r))->execute($this->collectionId(), $this->firstId(), null, $this->now());
        (new AddMedia($r))->execute($this->collectionId(), $this->thirdId(), MediaType::Image, $this->checksum(3), MediaOrder::fromInt(2), null, MediaSource::Owner, $this->now());
        (new RemoveMedia($r))->execute($this->collectionId(), $this->secondId(), $this->thirdId(), $this->now());

        self::assertSame(MediaStatus::Removed, $r->find($this->collectionId())?->items()[1]->status);
    }

    public function test_unknown_property_prevents_collection_creation(): void
    {
        $this->expectException(PropertyUnavailable::class);
        (new CreateMediaCollection(new FakeMediaCollectionRegistry, new FakePropertyCatalog([])))->execute($this->collectionId(), $this->propertyId(), $this->now());
    }

    public function test_duplicate_collection_identity_is_explicit(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $this->expectException(MediaCollectionIdConflict::class);
        $this->create($r);
    }

    public function test_unknown_collection_is_reported(): void
    {
        $this->expectException(MediaCollectionNotFound::class);
        (new AddMedia(new FakeMediaCollectionRegistry))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
    }

    public function test_stale_save_is_rejected_and_winner_is_preserved(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $first = $r->find($this->collectionId());
        $stale = $r->find($this->collectionId());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $first->add($this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
        $r->save($first, 0);
        $stale->add($this->secondId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
        try {
            $r->save($stale, 0);
            self::fail();
        } catch (ConcurrentMediaCollectionModification) {
            self::assertSame($this->firstId()->value, $r->find($this->collectionId())?->primary()?->id->value);
        }
    }

    public function test_failed_save_leaves_no_visible_mutation(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $r->failNextSave();
        try {
            (new AddMedia($r))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
            self::fail();
        } catch (ConcurrentMediaCollectionModification) {
            self::assertSame([], $r->find($this->collectionId())?->items());
            self::assertSame(0, $r->find($this->collectionId())?->version());
        }
    }

    public function test_reloaded_collection_never_replays_events(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $changed = (new AddMedia($r))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
        self::assertCount(2, $changed->releaseEvents());
        self::assertSame([], $r->find($this->collectionId())?->releaseEvents());
    }

    public function test_same_media_identity_cannot_belong_to_two_collections(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $this->createSecond($r);
        (new AddMedia($r))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());

        $this->expectException(MediaIdConflict::class);
        (new AddMedia($r))->execute($this->secondCollectionId(), $this->firstId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
    }

    public function test_failed_media_save_leaks_neither_mutation_nor_identity_reservation(): void
    {
        $r = new FakeMediaCollectionRegistry;
        $this->create($r);
        $this->createSecond($r);
        $r->failNextSave();
        try {
            (new AddMedia($r))->execute($this->collectionId(), $this->firstId(), MediaType::Image, $this->checksum(1), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
            self::fail();
        } catch (ConcurrentMediaCollectionModification) {
            self::assertSame([], $r->find($this->collectionId())?->items());
        }

        (new AddMedia($r))->execute($this->secondCollectionId(), $this->firstId(), MediaType::Image, $this->checksum(2), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->now());
        self::assertSame($this->firstId()->value, $r->find($this->secondCollectionId())?->primary()?->id->value);
    }

    private function create(FakeMediaCollectionRegistry $r): MediaCollection
    {
        return (new CreateMediaCollection($r, new FakePropertyCatalog([$this->propertyId()->value])))->execute($this->collectionId(), $this->propertyId(), $this->now());
    }

    private function createSecond(FakeMediaCollectionRegistry $r): MediaCollection
    {
        return (new CreateMediaCollection($r, new FakePropertyCatalog([$this->propertyId()->value])))->execute($this->secondCollectionId(), $this->propertyId(), $this->now());
    }

    private function collectionId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('60000000-0000-4000-8000-000000000001');
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function secondCollectionId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('60000000-0000-4000-8000-000000000099');
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
}
