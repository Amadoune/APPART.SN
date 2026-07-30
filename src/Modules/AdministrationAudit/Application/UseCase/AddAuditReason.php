<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use DateTimeImmutable;

final readonly class AddAuditReason extends AdministrativeActionUseCase
{
    public function execute(AdministrativeActionId $id, AuditReason $reason, DateTimeImmutable $at): AdministrativeAction
    {
        $action = $this->action($id);
        $version = $action->version();
        $action->addReason($reason, $at);

        return $this->save($action, $version);
    }
}
