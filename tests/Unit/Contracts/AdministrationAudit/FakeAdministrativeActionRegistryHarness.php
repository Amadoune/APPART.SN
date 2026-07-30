<?php

namespace Tests\Unit\Contracts\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Policy\ActionTypeFourEyesPolicy;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Unit\Modules\AdministrationAudit\Support\FakeAdministrativeActionRegistry;

class FakeAdministrativeActionRegistryHarness implements AdministrativeActionRegistryHarness
{
    public function freshRegistry(): AdministrativeActionRegistry
    {
        return new FakeAdministrativeActionRegistry;
    }

    public function minimalAction(?AdministrativeActionId $id = null): AdministrativeAction
    {
        $type = ActionType::fromString('role_assignment_review');

        return AdministrativeAction::initiate(
            $id ?? $this->primaryId(),
            ActorId::fromString('actor:contract-author'),
            TargetResourceId::fromString('identity:contract-account'),
            $type,
            new ActionTypeFourEyesPolicy([$type]),
            $this->at(0),
        );
    }

    public function actionWithHistory(?AdministrativeActionId $id = null): AdministrativeAction
    {
        $action = $this->minimalAction($id);
        $this->mutate($action);
        $action->record($this->at(2));

        return $action;
    }

    public function approvedAction(?AdministrativeActionId $id = null): AdministrativeAction
    {
        $action = $this->actionWithHistory($id);
        $action->approve(
            ApprovalId::fromString('31000000-0000-4000-8000-000000000101'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000102'),
            ActorId::fromString('actor:contract-reviewer'),
            $this->decisionReason(),
            $this->at(3),
        );

        return $action;
    }

    public function rejectedAction(?AdministrativeActionId $id = null): AdministrativeAction
    {
        $action = $this->actionWithHistory($id);
        $action->reject(
            DecisionId::fromString('31000000-0000-4000-8000-000000000103'),
            ActorId::fromString('actor:contract-reviewer'),
            $this->decisionReason(),
            $this->at(3),
        );

        return $action;
    }

    public function primaryId(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('31000000-0000-4000-8000-000000000001');
    }

    public function distinctId(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('31000000-0000-4000-8000-000000000002');
    }

    public function mutate(AdministrativeAction $action): void
    {
        $action->addReason(
            AuditReason::fromString('Fixed contract evidence for the administrative action.'),
            $this->at(1),
        );
    }

    public function mutateWithEvent(AdministrativeAction $action): void
    {
        $this->mutate($action);
        $action->record($this->at(2));
    }

    public function failNextWrite(AdministrativeActionRegistry $registry): void
    {
        Assert::assertInstanceOf(FakeAdministrativeActionRegistry::class, $registry);
        $registry->failNextSave();
    }

    private function decisionReason(): AuditReason
    {
        return AuditReason::fromString('Fixed independent decision evidence for the contract.');
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('2026-07-17T10:%02d:00+00:00', $minute));
    }
}
