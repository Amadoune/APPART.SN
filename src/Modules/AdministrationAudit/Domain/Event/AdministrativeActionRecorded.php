<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use DateTimeImmutable;

final readonly class AdministrativeActionRecorded extends AbstractAdministrativeActionEvent
{
    public function __construct(AdministrativeActionId $id, public ActorId $authorId, public TargetResourceId $targetId, public ActionType $actionType, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
