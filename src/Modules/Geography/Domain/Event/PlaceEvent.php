<?php

namespace Appart\Modules\Geography\Domain\Event;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use DateTimeImmutable;

interface PlaceEvent
{
    public function placeId(): PlaceId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;
}
