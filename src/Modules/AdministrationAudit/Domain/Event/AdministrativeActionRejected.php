<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;

final readonly class AdministrativeActionRejected extends AbstractAdministrativeActionEvent
{
    public function __construct(AdministrativeActionId $id, public DecisionId $decisionId, public ActorId $reviewerId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
