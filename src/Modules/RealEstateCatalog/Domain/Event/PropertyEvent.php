<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

interface PropertyEvent
{
    public function propertyId(): PropertyId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
