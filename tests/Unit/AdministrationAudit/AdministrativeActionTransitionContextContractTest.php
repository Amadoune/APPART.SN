<?php

namespace Tests\Unit\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppendInspection;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayOutcome;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionContextChecksum;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionTransitionContextContractTest extends TestCase
{
    public function test_record_context_is_complete_versioned_and_deterministic(): void
    {
        $context = $this->recordContext();
        $same = $this->recordContext();

        self::assertSame(AdministrativeActionTransitionContextVersion::V1, $context->contractVersion);
        self::assertSame(3, $context->expectedVersion->value);
        self::assertSame(4, $context->expectedVersion->next());
        self::assertSame('record', $context->decisionIdentities->action->value);
        self::assertNull($context->decisionIdentities->approvalId);
        self::assertNull($context->decisionIdentities->decisionId);
        self::assertSame($context->decisionContext->checksum()->value, $same->decisionContext->checksum()->value);
        self::assertSame($context->checksum()->value, $same->checksum()->value);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $context->checksum()->value);
    }

    public function test_approve_and_reject_require_their_exact_decision_identities(): void
    {
        $approve = $this->approveContext();
        $reject = $this->rejectContext();

        self::assertSame('approve', $approve->decisionIdentities->action->value);
        self::assertNotNull($approve->decisionIdentities->approvalId);
        self::assertNotNull($approve->decisionIdentities->decisionId);
        self::assertSame('reject', $reject->decisionIdentities->action->value);
        self::assertNull($reject->decisionIdentities->approvalId);
        self::assertNotNull($reject->decisionIdentities->decisionId);
        self::assertNotSame($approve->checksum()->value, $reject->checksum()->value);
    }

    public function test_context_rejects_an_implicit_version_or_wrong_certified_actor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AdministrativeActionExpectedVersion(-1);
    }

    public function test_record_rejects_actor_different_from_certified_author(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionTransitionExecutionContext::record(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('other-actor'),
            $this->occurredAt(),
            $this->reason(),
            $this->directDecisionContext(),
        );
    }

    public function test_decision_rejects_actor_different_from_certified_decision_actor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionTransitionExecutionContext::approve(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('author-001'),
            $this->occurredAt(),
            $this->approvalId(),
            $this->decisionId(),
            $this->reason(),
            $this->independentDecisionContext(),
        );
    }

    public function test_inspection_results_are_closed_and_snapshot_is_exclusive_to_found(): void
    {
        $snapshot = $this->snapshot($this->recordContext(), $this->recordTransition());
        $found = AdministrativeActionContextualInspectionResult::found($snapshot);
        $missing = AdministrativeActionContextualInspectionResult::missing($snapshot->actionId);
        $corrupted = AdministrativeActionContextualInspectionResult::corrupted($snapshot->actionId);

        self::assertSame(AdministrativeActionContextualInspectionStatus::Found, $found->status);
        self::assertSame($snapshot, $found->snapshot);
        self::assertSame(AdministrativeActionContextualInspectionStatus::Missing, $missing->status);
        self::assertNull($missing->snapshot);
        self::assertSame(AdministrativeActionContextualInspectionStatus::Corrupted, $corrupted->status);
        self::assertNull($corrupted->snapshot);
    }

    public function test_replay_is_already_applied_only_for_exact_action_version_and_context(): void
    {
        $context = $this->recordContext();
        $outcome = (new AdministrativeActionReplayPolicy)->classify(
            AdministrativeActionLifecycleAction::Record,
            $context,
            $this->snapshot($context, $this->recordTransition()),
        );

        self::assertSame(AdministrativeActionReplayOutcome::AlreadyApplied, $outcome);
    }

    public function test_replay_distinguishes_version_transition_and_context_divergence(): void
    {
        $policy = new AdministrativeActionReplayPolicy;
        $record = $this->recordContext();
        $wrongVersion = new AdministrativeActionContextualAppendInspection(
            $this->actionId(),
            5,
            $this->recordTransition(),
            $record,
            $record->checksum(),
        );
        self::assertSame(
            AdministrativeActionReplayOutcome::VersionConflict,
            $policy->classify(AdministrativeActionLifecycleAction::Record, $record, $wrongVersion),
        );
        self::assertSame(
            AdministrativeActionReplayOutcome::TransitionDivergence,
            $policy->classify(AdministrativeActionLifecycleAction::Approve, $record, $this->snapshot($record, $this->recordTransition())),
        );

        $differentContext = AdministrativeActionTransitionExecutionContext::record(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('author-001'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T11:01:00.000000+00:00')),
            $this->reason(),
            $this->directDecisionContext(),
        );
        self::assertSame(
            AdministrativeActionReplayOutcome::ContextDivergence,
            $policy->classify(
                AdministrativeActionLifecycleAction::Record,
                $differentContext,
                $this->snapshot($record, $this->recordTransition()),
            ),
        );
    }

    public function test_checksum_type_rejects_non_sha256_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionTransitionContextChecksum::fromString('invalid');
    }

    private function recordContext(): AdministrativeActionTransitionExecutionContext
    {
        return AdministrativeActionTransitionExecutionContext::record(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('author-001'),
            $this->occurredAt(),
            $this->reason(),
            $this->directDecisionContext(),
        );
    }

    private function approveContext(): AdministrativeActionTransitionExecutionContext
    {
        return AdministrativeActionTransitionExecutionContext::approve(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('decision-actor-001'),
            $this->occurredAt(),
            $this->approvalId(),
            $this->decisionId(),
            $this->reason(),
            $this->independentDecisionContext(),
        );
    }

    private function rejectContext(): AdministrativeActionTransitionExecutionContext
    {
        return AdministrativeActionTransitionExecutionContext::reject(
            new AdministrativeActionExpectedVersion(3),
            ActorId::fromString('decision-actor-001'),
            $this->occurredAt(),
            $this->decisionId(),
            $this->reason(),
            $this->independentDecisionContext(),
        );
    }

    private function directDecisionContext(): AdministrativeActionDecisionContext
    {
        $author = ActorId::fromString('author-001');

        return new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::directRecording($author, $author),
        );
    }

    private function independentDecisionContext(): AdministrativeActionDecisionContext
    {
        return new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::independentApprovalRequired(
                ActorId::fromString('author-001'),
                ActorId::fromString('decision-actor-001'),
            ),
        );
    }

    private function occurredAt(): AdministrativeActionHistoricalMirrorOccurredAt
    {
        return AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-24T11:00:00.000000+00:00'),
        );
    }

    private function reason(): AuditReason
    {
        return AuditReason::fromString('Explicit historical decision reason.');
    }

    private function approvalId(): ApprovalId
    {
        return ApprovalId::fromString('11111111-1111-4111-8111-111111111111');
    }

    private function decisionId(): DecisionId
    {
        return DecisionId::fromString('22222222-2222-4222-8222-222222222222');
    }

    private function actionId(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('33333333-3333-4333-8333-333333333333');
    }

    private function recordTransition(): AdministrativeActionLifecycleTransition
    {
        return new AdministrativeActionLifecycleTransition(
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleAction::Record,
        );
    }

    private function snapshot(
        AdministrativeActionTransitionExecutionContext $context,
        AdministrativeActionLifecycleTransition $transition,
    ): AdministrativeActionContextualAppendInspection {
        return new AdministrativeActionContextualAppendInspection(
            $this->actionId(),
            4,
            $transition,
            $context,
            $context->checksum(),
        );
    }
}
