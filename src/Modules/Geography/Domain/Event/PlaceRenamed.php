<?php

namespace Appart\Modules\Geography\Domain\Event;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use DateTimeImmutable;

final readonly class PlaceRenamed implements PlaceEvent
{
    public function __construct(
        private PlaceId $placeId,
        public PlaceName $previousName,
        public PlaceName $newName,
        private DateTimeImmutable $occurredAt,
        private int $aggregateVersion,
    ) {}

    public function placeId(): PlaceId
    {
        return $this->placeId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
