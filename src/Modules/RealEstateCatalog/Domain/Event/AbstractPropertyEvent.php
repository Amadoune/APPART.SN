<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

abstract readonly class AbstractPropertyEvent implements PropertyEvent
{
    private PropertyEventMetadata $metadata;

    public function __construct(private PropertyId $id, private DateTimeImmutable $at)
    {
        $this->metadata = new PropertyEventMetadata;
    }

    public function propertyId(): PropertyId
    {
        return $this->id;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->at;
    }

    public function aggregateVersion(): int
    {
        return $this->metadata->aggregateVersion;
    }

    public function eventIndex(): int
    {
        return $this->metadata->eventIndex;
    }

    final public function stamp(int $version, int $index): void
    {
        $this->metadata->aggregateVersion = $version;
        $this->metadata->eventIndex = $index;
    }
}
