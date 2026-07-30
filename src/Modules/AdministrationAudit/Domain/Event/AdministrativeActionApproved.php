<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;

final readonly class AdministrativeActionApproved extends AbstractAdministrativeActionEvent
{
    public function __construct(AdministrativeActionId $id, public ApprovalId $approvalId, public DecisionId $decisionId, public ActorId $approverId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
