<?php

namespace Appart\Modules\AdministrationAudit\Domain\Model;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionOutcome;
use DateTimeImmutable;

final readonly class Decision
{
    public function __construct(public DecisionId $id, public DecisionOutcome $outcome, public ActorId $decidedBy, public AuditReason $reason, public DateTimeImmutable $decidedAt) {}
}
