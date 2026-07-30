<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;

final readonly class FourEyesSatisfied extends AbstractAdministrativeActionEvent
{
    public function __construct(AdministrativeActionId $id, public ActorId $authorId, public ActorId $approverId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
