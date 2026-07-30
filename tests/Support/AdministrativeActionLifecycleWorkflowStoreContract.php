<?php

namespace Tests\Support;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentResult;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class AdministrativeActionLifecycleWorkflowStoreContract extends TestCase
{
    abstract protected function store(): AdministrativeActionLifecycleWorkflowStore;

    abstract protected function seedDraft(bool $requiresFourEyes): AdministrativeActionLifecycleEnrollmentCheckpoint;

    abstract protected function resetStore(): void;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetStore();
    }

    public function test_unknown_action_is_not_enrolled(): void
    {
        self::assertSame(
            AdministrativeActionLifecyclePersistenceReadStatus::NotEnrolled,
            $this->store()->read($this->actionId())->status,
        );
    }

    public function test_enrollment_is_exact_readable_and_idempotent(): void
    {
        $checkpoint = $this->seedDraft(false);
        self::assertSame(AdministrativeActionLifecycleEnrollmentResult::Enrolled, $this->store()->enroll($checkpoint));
        self::assertSame(AdministrativeActionLifecycleEnrollmentResult::AlreadyEnrolled, $this->store()->enroll($checkpoint));

        $read = $this->store()->read($this->actionId());
        self::assertSame(AdministrativeActionLifecyclePersistenceReadStatus::Found, $read->status);
        self::assertSame($checkpoint->historicalVersion, $read->snapshot?->version);
        self::assertSame(AdministrativeActionLifecycleState::Draft, $read->snapshot?->state);
    }

    public function test_divergent_enrollment_is_rejected(): void
    {
        $checkpoint = $this->seedDraft(false);
        $this->store()->enroll($checkpoint);
        $divergent = new AdministrativeActionLifecycleEnrollmentCheckpoint(
            $checkpoint->actionId,
            $checkpoint->historicalVersion,
            AdministrativeActionLifecycleState::PendingApproval,
            $checkpoint->sourceChecksum,
        );

        self::assertSame(
            AdministrativeActionLifecycleEnrollmentResult::EnrollmentDivergence,
            $this->store()->enroll($divergent),
        );
    }

    public function test_record_is_applied_once_and_replay_is_idempotent(): void
    {
        $checkpoint = $this->seedDraft(false);
        $this->store()->enroll($checkpoint);
        $mutation = $this->recordMutation($checkpoint, $this->reason(), $this->occurredAt());

        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::Applied, $this->store()->append($mutation));
        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::AlreadyApplied, $this->store()->append($mutation));
        self::assertSame(AdministrativeActionLifecycleState::Recorded, $this->store()->read($this->actionId())->snapshot?->state);
        self::assertSame($checkpoint->historicalVersion + 1, $this->store()->read($this->actionId())->snapshot?->version);
    }

    public function test_same_transition_with_another_historical_payload_is_divergent(): void
    {
        $checkpoint = $this->seedDraft(false);
        $this->store()->enroll($checkpoint);
        $this->store()->append($this->recordMutation($checkpoint, $this->reason(), $this->occurredAt()));

        self::assertSame(
            AdministrativeActionHistoricalMirrorWriteResult::MirrorDivergence,
            $this->store()->append($this->recordMutation(
                $checkpoint,
                AuditReason::fromString('A distinct explicit historical reason.'),
                $this->occurredAt(),
            )),
        );
    }

    public function test_version_and_state_conflicts_are_distinct(): void
    {
        $checkpoint = $this->seedDraft(true);
        $this->store()->enroll($checkpoint);
        $versionConflict = AdministrativeActionHistoricalMirrorMutation::record(
            $checkpoint->actionId,
            $checkpoint->historicalVersion - 1,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->author(),
            $this->reason(),
            $this->occurredAt(),
        );
        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::VersionConflict, $this->store()->append($versionConflict));

        $stateConflict = AdministrativeActionHistoricalMirrorMutation::approve(
            $checkpoint->actionId,
            $checkpoint->historicalVersion,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            ),
            ActorId::fromString('actor:contract-reviewer'),
            AuditReason::fromString('Fixed independent decision evidence for the contract.'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:03:00Z')),
            ApprovalId::fromString('31000000-0000-4000-8000-000000000101'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000102'),
        );
        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::StateConflict, $this->store()->append($stateConflict));
    }

    protected function actionId(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('31000000-0000-4000-8000-000000000001');
    }

    protected function author(): ActorId
    {
        return ActorId::fromString('actor:contract-author');
    }

    protected function reason(): AuditReason
    {
        return AuditReason::fromString('Fixed contract evidence for the administrative action.');
    }

    protected function occurredAt(): AdministrativeActionHistoricalMirrorOccurredAt
    {
        return AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-17T10:02:00Z'),
        );
    }

    protected function recordMutation(
        AdministrativeActionLifecycleEnrollmentCheckpoint $checkpoint,
        AuditReason $reason,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
    ): AdministrativeActionHistoricalMirrorMutation {
        return AdministrativeActionHistoricalMirrorMutation::record(
            $checkpoint->actionId,
            $checkpoint->historicalVersion,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::Recorded,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->author(),
            $reason,
            $occurredAt,
        );
    }
}
