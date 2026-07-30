<?php

namespace Appart\Modules\Media\Application\Contract;

use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;

interface MediaCollectionRegistry
{
    /** Returns a detached collection without persisted domain events. */
    public function find(MediaCollectionId $id): ?MediaCollection;

    /** Atomically adds a unique MediaCollectionId. */
    public function add(MediaCollection $collection): void;

    /** Conditionally saves a clean snapshot when expectedVersion matches. */
    public function save(MediaCollection $collection, int $expectedVersion): void;

    /**
     * Atomically reserves MediaId forever for this collection and conditionally saves it.
     * Neither the reservation nor the mutation becomes visible when the operation fails.
     *
     * @throws MediaIdConflict
     * @throws ConcurrentMediaCollectionModification
     */
    public function saveWithMediaReservation(MediaCollection $collection, MediaId $mediaId, int $expectedVersion): void;
}
