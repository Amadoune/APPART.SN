<?php

namespace Appart\Modules\Geography\Application\PlaceLifecyclePersistence;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final readonly class PlaceLifecyclePersistenceReadResult
{
    private function __construct(
        public PlaceId $placeId,
        public PlaceLifecyclePersistenceReadStatus $status,
        public ?PlaceLifecycleStoredState $snapshot,
    ) {}

    public static function found(PlaceLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->placeId, PlaceLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(PlaceId $placeId): self
    {
        return new self($placeId, PlaceLifecyclePersistenceReadStatus::Missing, null);
    }

    public static function corrupted(PlaceId $placeId): self
    {
        return new self($placeId, PlaceLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
