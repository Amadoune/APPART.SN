<?php

namespace Appart\Modules\Geography\Domain\Event;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use DateTimeImmutable;

final readonly class PlaceEnabled implements PlaceEvent
{
    public function __construct(
        private PlaceId $placeId,
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
