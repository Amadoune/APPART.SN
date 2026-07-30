<?php

namespace Appart\Modules\AdministrationAudit\Application\UseCase;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Policy\FourEyesPolicy;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use DateTimeImmutable;

final readonly class CreateAdministrativeAction
{
    public function __construct(private AdministrativeActionRegistry $actions, private FourEyesPolicy $fourEyesPolicy) {}

    public function execute(AdministrativeActionId $id, ActorId $author, TargetResourceId $target, ActionType $type, DateTimeImmutable $at): AdministrativeAction
    {
        $action = AdministrativeAction::initiate($id, $author, $target, $type, $this->fourEyesPolicy, $at);
        $this->actions->add($action);

        return $action;
    }
}
