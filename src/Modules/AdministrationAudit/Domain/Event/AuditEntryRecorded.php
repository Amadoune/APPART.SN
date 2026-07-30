<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use DateTimeImmutable;

final readonly class AuditEntryRecorded extends AbstractAdministrativeActionEvent
{
    public function __construct(AdministrativeActionId $id, public int $sequence, public string $fact, public ActorId $actorId, public AuditReason $reason, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
