<?php

namespace Tests\Unit\AdministrativeActionHistoricalMirror;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentMutationPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentMutationPolicyResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorContractVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutationKind;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionPersistenceTransactionMode;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceOperation;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionHistoricalMirrorContractTest extends TestCase
{
    public function test_record_payload_is_exact_and_deterministic(): void
    {
        $first = $this->record();
        $second = $this->record();

        self::assertSame(AdministrativeActionHistoricalMirrorContractVersion::V1, $first->contractVersion);
        self::assertSame(AdministrativeActionHistoricalMirrorMutationKind::Record, $first->kind);
        self::assertNull($first->approvalId);
        self::assertNull($first->decisionId);
        self::assertEquals($first, $second);
        self::assertEquals($first->checksum(), $second->checksum());
    }

    public function test_approve_payload_requires_both_child_identities(): void
    {
        $mutation = AdministrativeActionHistoricalMirrorMutation::approve(
            $this->actionId(),
            4,
            $this->transition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            ),
            $this->actor('decider-001'),
            $this->reason(),
            $this->occurredAt(),
            ApprovalId::fromString('a47b2000-0000-4000-8000-000000000002'),
            DecisionId::fromString('a47b2000-0000-4000-8000-000000000003'),
        );

        self::assertSame(AdministrativeActionHistoricalMirrorMutationKind::Approve, $mutation->kind);
        self::assertNotNull($mutation->approvalId);
        self::assertNotNull($mutation->decisionId);
    }

    public function test_reject_payload_has_only_the_decision_identity(): void
    {
        $mutation = AdministrativeActionHistoricalMirrorMutation::reject(
            $this->actionId(),
            4,
            $this->transition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Rejected,
                AdministrativeActionLifecycleAction::Reject,
            ),
            $this->actor('decider-001'),
            $this->reason(),
            $this->occurredAt(),
            DecisionId::fromString('a47b2000-0000-4000-8000-000000000003'),
        );

        self::assertSame(AdministrativeActionHistoricalMirrorMutationKind::Reject, $mutation->kind);
        self::assertNull($mutation->approvalId);
        self::assertNotNull($mutation->decisionId);
    }

    public function test_mutation_rejects_a_transition_for_another_action(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionHistoricalMirrorMutation::record(
            $this->actionId(),
            1,
            $this->transition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            ),
            $this->actor('author-001'),
            $this->reason(),
            $this->occurredAt(),
        );
    }

    public function test_mutation_rejects_a_negative_expected_version(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionHistoricalMirrorMutation::record(
            $this->actionId(),
            -1,
            $this->record()->transition,
            $this->actor('author-001'),
            $this->reason(),
            $this->occurredAt(),
        );
    }

    public function test_occurred_at_must_be_explicit_utc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-24T12:00:00+02:00'),
        );
    }

    public function test_checkpoint_canonicalization_is_stable_and_versioned(): void
    {
        $canonicalizer = new AdministrativeActionEnrollmentCanonicalizer;
        $checkpoint = $canonicalizer->checkpoint(
            $this->actionId(),
            3,
            AdministrativeActionLifecycleState::Draft,
        );

        self::assertSame(
            'administrative-action-lifecycle-enrollment-v1'."\n".$this->actionId()->value."\n3\ndraft",
            $canonicalizer->canonicalSource($this->actionId(), 3, AdministrativeActionLifecycleState::Draft),
        );
        self::assertSame(
            hash('sha256', $canonicalizer->canonicalSource($this->actionId(), 3, AdministrativeActionLifecycleState::Draft)),
            $checkpoint->sourceChecksum->value,
        );
    }

    #[DataProvider('postEnrollmentPolicy')]
    public function test_post_enrollment_policy_is_closed(
        AdministrativeActionPersistenceOperation $operation,
        AdministrativeActionEnrollmentMutationPolicyResult $expected,
    ): void {
        self::assertSame(
            $expected,
            (new AdministrativeActionEnrollmentMutationPolicy)->classify($operation, true),
        );
    }

    public function test_closed_write_results_cover_divergence_and_corruption(): void
    {
        self::assertSame([
            'applied',
            'already_applied',
            'version_conflict',
            'state_conflict',
            'mirror_divergence',
            'enrollment_divergence',
            'corrupted',
            'persistence_corrupted',
        ], array_column(AdministrativeActionHistoricalMirrorWriteResult::cases(), 'value'));
    }

    public function test_transaction_modes_are_explicit(): void
    {
        self::assertSame(['local', 'external'], array_column(AdministrativeActionPersistenceTransactionMode::cases(), 'value'));
    }

    /** @return iterable<string, array{AdministrativeActionPersistenceOperation, AdministrativeActionEnrollmentMutationPolicyResult}> */
    public static function postEnrollmentPolicy(): iterable
    {
        yield 'creation locked' => [AdministrativeActionPersistenceOperation::Creation, AdministrativeActionEnrollmentMutationPolicyResult::MutationRejected];
        yield 'reason locked' => [AdministrativeActionPersistenceOperation::ReasonMutation, AdministrativeActionEnrollmentMutationPolicyResult::MutationRejected];
        yield 'audit locked' => [AdministrativeActionPersistenceOperation::AuditDetailMutation, AdministrativeActionEnrollmentMutationPolicyResult::MutationRejected];
        yield 'enrollment locked' => [AdministrativeActionPersistenceOperation::LifecycleEnrollment, AdministrativeActionEnrollmentMutationPolicyResult::MutationRejected];
        yield 'lifecycle read' => [AdministrativeActionPersistenceOperation::LifecycleRead, AdministrativeActionEnrollmentMutationPolicyResult::LifecycleAuthorityRequired];
        yield 'transition' => [AdministrativeActionPersistenceOperation::LifecycleTransition, AdministrativeActionEnrollmentMutationPolicyResult::LifecycleAuthorityRequired];
        yield 'compatibility read' => [AdministrativeActionPersistenceOperation::CompatibilityRead, AdministrativeActionEnrollmentMutationPolicyResult::CompatibilityReadAllowed];
    }

    private function record(): AdministrativeActionHistoricalMirrorMutation
    {
        return AdministrativeActionHistoricalMirrorMutation::record(
            $this->actionId(),
            3,
            $this->transition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::Recorded,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->actor('author-001'),
            $this->reason(),
            $this->occurredAt(),
        );
    }

    private function actionId(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('a47b2000-0000-4000-8000-000000000001');
    }

    private function actor(string $value): ActorId
    {
        return ActorId::fromString($value);
    }

    private function reason(): AuditReason
    {
        return AuditReason::fromString('Explicit historical audit reason.');
    }

    private function occurredAt(): AdministrativeActionHistoricalMirrorOccurredAt
    {
        return AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-24T10:00:00.123456Z'),
        );
    }

    private function transition(
        AdministrativeActionLifecycleState $from,
        AdministrativeActionLifecycleState $to,
        AdministrativeActionLifecycleAction $action,
    ): AdministrativeActionLifecycleTransition {
        return new AdministrativeActionLifecycleTransition($from, $to, $action);
    }
}
