<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;

final readonly class RecordAdministrativeAction extends AdministrativeActionUseCase
{
    public function execute(AdministrativeActionId $id, DateTimeImmutable $at): AdministrativeAction
    {
        $action = $this->action($id);
        $version = $action->version();
        $action->record($at);

        return $this->save($action, $version);
    }
}
