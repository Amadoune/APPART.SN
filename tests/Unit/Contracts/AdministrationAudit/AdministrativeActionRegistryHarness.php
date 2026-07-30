<?php

namespace Tests\Unit\Contracts\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

interface AdministrativeActionRegistryHarness
{
    public function freshRegistry(): AdministrativeActionRegistry;

    public function minimalAction(?AdministrativeActionId $id = null): AdministrativeAction;

    public function actionWithHistory(?AdministrativeActionId $id = null): AdministrativeAction;

    public function approvedAction(?AdministrativeActionId $id = null): AdministrativeAction;

    public function rejectedAction(?AdministrativeActionId $id = null): AdministrativeAction;

    public function primaryId(): AdministrativeActionId;

    public function distinctId(): AdministrativeActionId;

    public function mutate(AdministrativeAction $action): void;

    public function mutateWithEvent(AdministrativeAction $action): void;

    public function failNextWrite(AdministrativeActionRegistry $registry): void;
}
