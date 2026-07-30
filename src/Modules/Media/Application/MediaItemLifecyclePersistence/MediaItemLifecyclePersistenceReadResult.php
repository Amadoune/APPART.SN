<?php

namespace Appart\Modules\Media\Application\MediaItemLifecyclePersistence;

final readonly class MediaItemLifecyclePersistenceReadResult
{
    private function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecyclePersistenceReadStatus $status,
        public ?MediaItemLifecycleStoredState $snapshot,
    ) {}

    public static function found(MediaItemLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->mediaId, MediaItemLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(MediaItemLifecycleId $mediaId): self
    {
        return new self($mediaId, MediaItemLifecyclePersistenceReadStatus::Missing, null);
    }

    public static function corrupted(MediaItemLifecycleId $mediaId): self
    {
        return new self($mediaId, MediaItemLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
