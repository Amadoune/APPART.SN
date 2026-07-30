<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;

final readonly class ApproveAdministrativeAction extends AdministrativeActionUseCase
{
    public function execute(AdministrativeActionId $id, ApprovalId $approvalId, DecisionId $decisionId, ActorId $approver, AuditReason $reason, DateTimeImmutable $at): AdministrativeAction
    {
        $action = $this->action($id);
        $version = $action->version();
        $action->approve($approvalId, $decisionId, $approver, $reason, $at);

        return $this->save($action, $version);
    }
}
