<?php

namespace Tests\Unit\Modules\Media\Support;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;

final class FakeMediaCollectionRegistry implements MediaCollectionRegistry
{
    /** @var array<string, MediaCollection> */
    private array $collections = [];

    /** @var array<string, string> media id => collection id */
    private array $mediaOwners = [];

    private bool $failNextSave = false;

    public function find(MediaCollectionId $id): ?MediaCollection
    {
        return isset($this->collections[$id->value]) ? clone $this->collections[$id->value] : null;
    }

    public function add(MediaCollection $collection): void
    {
        if (isset($this->collections[$collection->id()->value])) {
            throw new MediaCollectionIdConflict;
        }
        $this->collections[$collection->id()->value] = $this->cleanSnapshot($collection);
    }

    public function save(MediaCollection $collection, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentMediaCollectionModification;
        }
        $stored = $this->collections[$collection->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentMediaCollectionModification;
        }
        $this->collections[$collection->id()->value] = $this->cleanSnapshot($collection);
    }

    public function saveWithMediaReservation(MediaCollection $collection, MediaId $mediaId, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentMediaCollectionModification;
        }
        $stored = $this->collections[$collection->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentMediaCollectionModification;
        }
        $owner = $this->mediaOwners[$mediaId->value] ?? null;
        if ($owner !== null && $owner !== $collection->id()->value) {
            throw new MediaIdConflict;
        }
        $this->collections[$collection->id()->value] = $this->cleanSnapshot($collection);
        $this->mediaOwners[$mediaId->value] = $collection->id()->value;
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }

    private function cleanSnapshot(MediaCollection $collection): MediaCollection
    {
        $snapshot = clone $collection;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
