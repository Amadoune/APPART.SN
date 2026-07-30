<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;

final readonly class RejectAdministrativeAction extends AdministrativeActionUseCase
{
    public function execute(AdministrativeActionId $id, DecisionId $decisionId, ActorId $reviewer, AuditReason $reason, DateTimeImmutable $at): AdministrativeAction
    {
        $action = $this->action($id);
        $version = $action->version();
        $action->reject($decisionId, $reviewer, $reason, $at);

        return $this->save($action, $version);
    }
}
