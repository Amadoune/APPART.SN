<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

abstract readonly class AbstractProfessionalEvent implements ProfessionalEvent
{
    private ProfessionalEventMetadata $metadata;

    public function __construct(private ProfessionalId $professionalId, private DateTimeImmutable $at)
    {
        $this->metadata = new ProfessionalEventMetadata;
    }

    public function professionalId(): ProfessionalId
    {
        return $this->professionalId;
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
