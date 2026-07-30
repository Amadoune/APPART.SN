<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionNotFound;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

abstract readonly class AdministrativeActionUseCase
{
    public function __construct(protected AdministrativeActionRegistry $actions) {}

    protected function action(AdministrativeActionId $id): AdministrativeAction
    {
        return $this->actions->find($id) ?? throw new AdministrativeActionNotFound;
    }

    protected function save(AdministrativeAction $action, int $version): AdministrativeAction
    {
        $this->actions->save($action, $version);

        return $action;
    }
}
