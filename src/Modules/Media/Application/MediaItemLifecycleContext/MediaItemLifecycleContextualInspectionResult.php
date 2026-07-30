<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;

final readonly class MediaItemLifecycleContextualInspectionResult
{
    private function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecycleContextualInspectionStatus $status,
        public ?MediaItemLifecycleContextualAppendInspection $snapshot,
    ) {}

    public static function found(MediaItemLifecycleContextualAppendInspection $snapshot): self
    {
        return new self($snapshot->mediaId, MediaItemLifecycleContextualInspectionStatus::Found, $snapshot);
    }

    public static function missing(MediaItemLifecycleId $mediaId): self
    {
        return new self($mediaId, MediaItemLifecycleContextualInspectionStatus::Missing, null);
    }

    public static function corrupted(MediaItemLifecycleId $mediaId): self
    {
        return new self($mediaId, MediaItemLifecycleContextualInspectionStatus::Corrupted, null);
    }
}
