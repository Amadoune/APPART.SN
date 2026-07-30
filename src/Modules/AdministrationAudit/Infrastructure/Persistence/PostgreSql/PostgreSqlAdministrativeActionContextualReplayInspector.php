<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

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
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionContextChecksum;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final readonly class PostgreSqlAdministrativeActionContextualReplayInspector implements AdministrativeActionContextualReplayInspector
{
    public function __construct(
        private PDO $connection,
        private AdministrativeActionLifecycleWorkflowMapper $workflowMapper,
    ) {}

    public function inspectLatest(AdministrativeActionId $actionId): AdministrativeActionContextualInspectionResult
    {
        $statement = $this->connection->prepare('SELECT t.action_id::text,t.version,t.previous_state,t.current_state,t.action,t.entry_checksum,c.contract_version,c.expected_version,c.actor_id,c.occurred_at,c.transition_action,c.approval_id::text,c.decision_id::text,c.historical_reason,c.decision_context_version,c.reason_evidence,c.recording_disposition,c.author_id,c.decision_actor_id,c.decision_context_checksum,c.context_checksum FROM administration_audit.administrative_action_lifecycle_transitions t JOIN administration_audit.administrative_action_lifecycle_transition_contexts c USING(action_id,version) WHERE t.action_id=:action_id AND t.entry_kind=\'transition\' ORDER BY t.version DESC LIMIT 1');
        $statement->execute(['action_id' => $actionId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return AdministrativeActionContextualInspectionResult::missing($actionId);
        }

        try {
            $this->workflowMapper->storedState($row);
            $context = $this->restoreContext($row);
            if (! hash_equals((string) $row['decision_context_checksum'], $context->decisionContext->checksum()->value)
                || ! hash_equals((string) $row['context_checksum'], $context->checksum()->value)) {
                return AdministrativeActionContextualInspectionResult::corrupted($actionId);
            }

            return AdministrativeActionContextualInspectionResult::found(new AdministrativeActionContextualAppendInspection(
                $actionId,
                (int) $row['version'],
                new AdministrativeActionLifecycleTransition(
                    AdministrativeActionLifecycleState::from((string) $row['previous_state']),
                    AdministrativeActionLifecycleState::from((string) $row['current_state']),
                    AdministrativeActionLifecycleAction::from((string) $row['action']),
                ),
                $context,
                AdministrativeActionTransitionContextChecksum::fromString((string) $row['context_checksum']),
            ));
        } catch (Throwable) {
            return AdministrativeActionContextualInspectionResult::corrupted($actionId);
        }
    }

    /** @param array<string, mixed> $row */
    private function restoreContext(array $row): AdministrativeActionTransitionExecutionContext
    {
        $author = ActorId::fromString((string) $row['author_id']);
        $decisionActor = ActorId::fromString((string) $row['decision_actor_id']);
        $disposition = (string) $row['recording_disposition'];
        $authority = $disposition === 'direct_recording'
            ? AdministrativeActionDecisionAuthority::directRecording($author, $decisionActor)
            : AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decisionActor);
        $decisionContext = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::from((int) $row['decision_context_version']),
            AdministrativeActionReasonEvidence::from((string) $row['reason_evidence']),
            $authority,
        );
        $expectedVersion = new AdministrativeActionExpectedVersion((int) $row['expected_version']);
        $actor = ActorId::fromString((string) $row['actor_id']);
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            (new DateTimeImmutable((string) $row['occurred_at']))->setTimezone(new DateTimeZone('UTC')),
        );
        $reason = AuditReason::fromString((string) $row['historical_reason']);

        return match (AdministrativeActionLifecycleAction::from((string) $row['transition_action'])) {
            AdministrativeActionLifecycleAction::Record => AdministrativeActionTransitionExecutionContext::record(
                $expectedVersion, $actor, $occurredAt, $reason, $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Approve => AdministrativeActionTransitionExecutionContext::approve(
                $expectedVersion,
                $actor,
                $occurredAt,
                ApprovalId::fromString((string) $row['approval_id']),
                DecisionId::fromString((string) $row['decision_id']),
                $reason,
                $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Reject => AdministrativeActionTransitionExecutionContext::reject(
                $expectedVersion,
                $actor,
                $occurredAt,
                DecisionId::fromString((string) $row['decision_id']),
                $reason,
                $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Unknown => throw new \UnexpectedValueException('Unknown action persisted in contextual storage.'),
        };
    }
}
