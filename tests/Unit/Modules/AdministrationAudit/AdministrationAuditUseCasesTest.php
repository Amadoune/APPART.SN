<?php

namespace Tests\Unit\Modules\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\UseCase\AddAuditReason;
use Appart\Modules\AdministrationAudit\Application\UseCase\ApproveAdministrativeAction;
use Appart\Modules\AdministrationAudit\Application\UseCase\CreateAdministrativeAction;
use Appart\Modules\AdministrationAudit\Application\UseCase\RecordAdministrativeAction;
use Appart\Modules\AdministrationAudit\Application\UseCase\RejectAdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionNotFound;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Policy\ActionTypeFourEyesPolicy;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionStatus;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\AdministrationAudit\Support\FakeAdministrativeActionRegistry;

final class AdministrationAuditUseCasesTest extends TestCase
{
    public function test_use_cases_create_reason_record_and_approve(): void
    {
        $r = new FakeAdministrativeActionRegistry;
        $a = $this->create($r);
        (new AddAuditReason($r))->execute($a->id(), $this->reason(), $this->now());
        (new RecordAdministrativeAction($r))->execute($a->id(), $this->now());
        (new ApproveAdministrativeAction($r))->execute($a->id(), $this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        self::assertSame(AdministrativeActionStatus::Approved, $r->find($a->id())?->status());
    }

    public function test_rejection_use_case_records_a_final_decision(): void
    {
        $r = $this->recordedRegistry();
        (new RejectAdministrativeAction($r))->execute($this->id(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        self::assertSame(AdministrativeActionStatus::Rejected, $r->find($this->id())?->status());
    }

    public function test_unknown_action_is_reported(): void
    {
        $this->expectException(AdministrativeActionNotFound::class);
        (new RecordAdministrativeAction(new FakeAdministrativeActionRegistry))->execute($this->id(), $this->now());
    }

    public function test_stale_action_is_rejected_by_expected_version(): void
    {
        $r = $this->recordedRegistry();
        $first = $r->find($this->id());
        $stale = $r->find($this->id());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $version = $first->version();
        $first->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        $r->save($first, $version);
        $stale->reject(DecisionId::fromString('30000000-0000-4000-8000-000000000009'), $this->reviewer(), $this->decisionReason(), $this->now());
        $this->expectException(ConcurrentAdministrativeActionModification::class);
        $r->save($stale, $version);
    }

    public function test_duplicate_add_reports_an_identity_conflict(): void
    {
        $r = new FakeAdministrativeActionRegistry;
        $this->create($r);

        $this->expectException(AdministrativeActionIdentityConflict::class);
        $this->create($r);
    }

    public function test_failed_save_leaves_no_visible_mutation(): void
    {
        $r = new FakeAdministrativeActionRegistry;
        $a = $this->create($r);
        $r->failNextSave();

        try {
            (new AddAuditReason($r))->execute($a->id(), $this->reason(), $this->now());
            self::fail('The save should fail.');
        } catch (ConcurrentAdministrativeActionModification) {
            $stored = $r->find($a->id());
            self::assertNotNull($stored);
            self::assertNull($stored->reason());
            self::assertSame(0, $stored->version());
        }
    }

    public function test_policy_governs_four_eyes_from_action_type(): void
    {
        $r = new FakeAdministrativeActionRegistry;
        $governed = $this->create($r);
        (new AddAuditReason($r))->execute($governed->id(), $this->reason(), $this->now());
        (new RecordAdministrativeAction($r))->execute($governed->id(), $this->now());
        self::assertSame(AdministrativeActionStatus::PendingApproval, $r->find($governed->id())?->status());

        $ungovernedId = AdministrativeActionId::fromString('30000000-0000-4000-8000-000000000010');
        $ungoverned = (new CreateAdministrativeAction($r, $this->policy()))->execute($ungovernedId, $this->author(), $this->target(), ActionType::fromString('profile_note'), $this->now());
        (new AddAuditReason($r))->execute($ungoverned->id(), $this->reason(), $this->now());
        (new RecordAdministrativeAction($r))->execute($ungoverned->id(), $this->now());
        self::assertSame(AdministrativeActionStatus::Recorded, $r->find($ungoverned->id())?->status());
    }

    private function recordedRegistry(): FakeAdministrativeActionRegistry
    {
        $r = new FakeAdministrativeActionRegistry;
        $a = $this->create($r);
        (new AddAuditReason($r))->execute($a->id(), $this->reason(), $this->now());
        (new RecordAdministrativeAction($r))->execute($a->id(), $this->now());

        return $r;
    }

    private function create(FakeAdministrativeActionRegistry $registry): AdministrativeAction
    {
        return (new CreateAdministrativeAction($registry, $this->policy()))->execute($this->id(), $this->author(), $this->target(), $this->type(), $this->now());
    }

    private function policy(): ActionTypeFourEyesPolicy
    {
        return new ActionTypeFourEyesPolicy([$this->type()]);
    }

    private function id(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('30000000-0000-4000-8000-000000000001');
    }

    private function author(): ActorId
    {
        return ActorId::fromString('actor:author');
    }

    private function reviewer(): ActorId
    {
        return ActorId::fromString('actor:reviewer');
    }

    private function target(): TargetResourceId
    {
        return TargetResourceId::fromString('identity:account-42');
    }

    private function type(): ActionType
    {
        return ActionType::fromString('role_assignment_review');
    }

    private function reason(): AuditReason
    {
        return AuditReason::fromString('Administrative review requested with evidence.');
    }

    private function decisionReason(): AuditReason
    {
        return AuditReason::fromString('Independent reviewer validated all evidence.');
    }

    private function approvalId(): ApprovalId
    {
        return ApprovalId::fromString('30000000-0000-4000-8000-000000000002');
    }

    private function decisionId(): DecisionId
    {
        return DecisionId::fromString('30000000-0000-4000-8000-000000000003');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }
}
