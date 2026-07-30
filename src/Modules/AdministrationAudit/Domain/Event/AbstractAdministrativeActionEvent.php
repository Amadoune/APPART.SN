<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;

abstract readonly class AbstractAdministrativeActionEvent implements AdministrativeActionEvent
{
    private AdministrativeActionEventMetadata $metadata;

    public function __construct(private AdministrativeActionId $id, private DateTimeImmutable $at)
    {
        $this->metadata = new AdministrativeActionEventMetadata;
    }

    public function actionId(): AdministrativeActionId
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
