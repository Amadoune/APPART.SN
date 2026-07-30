<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutationKind;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\Contract\AdministrativeActionLifecycleAtomicPersistenceTransaction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentResult;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlAdministrativeActionLifecycleRepository implements AdministrativeActionLifecycleWorkflowStore
{
    private AdministrativeActionLifecycleAtomicPersistenceTransaction $transaction;

    public function __construct(
        private PDO $connection,
        private AdministrativeActionLifecycleWorkflowMapper $mapper,
        private AdministrativeActionEnrollmentCanonicalizer $canonicalizer,
        ?AdministrativeActionLifecycleAtomicPersistenceTransaction $transaction = null,
    ) {
        $this->transaction = $transaction ?? new PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction($connection);
    }

    public function enroll(
        AdministrativeActionLifecycleEnrollmentCheckpoint $checkpoint,
    ): AdministrativeActionLifecycleEnrollmentResult {
        try {
            return $this->transaction->run(function () use ($checkpoint): AdministrativeActionLifecycleEnrollmentResult {
                $this->lock($checkpoint->actionId);
                $historical = $this->historical($checkpoint->actionId);
                if ($historical === false) {
                    return AdministrativeActionLifecycleEnrollmentResult::SourceMissing;
                }

                $state = AdministrativeActionLifecycleState::tryFrom((string) $historical['status']);
                if ($state === null || (int) $historical['version'] < 0) {
                    return AdministrativeActionLifecycleEnrollmentResult::SourceCorrupted;
                }

                $expected = $this->canonicalizer->checkpoint(
                    $checkpoint->actionId,
                    (int) $historical['version'],
                    $state,
                );
                if ($expected != $checkpoint) {
                    return AdministrativeActionLifecycleEnrollmentResult::EnrollmentDivergence;
                }

                $current = $this->current($checkpoint->actionId, true);
                if ($current !== false) {
                    return $this->sameEnrollment($current, $checkpoint)
                        ? AdministrativeActionLifecycleEnrollmentResult::AlreadyEnrolled
                        : AdministrativeActionLifecycleEnrollmentResult::EnrollmentDivergence;
                }

                $this->insert($this->mapper->enrollment($checkpoint));

                return AdministrativeActionLifecycleEnrollmentResult::Enrolled;
            });
        } catch (PDOException) {
            return AdministrativeActionLifecycleEnrollmentResult::SourceUnavailable;
        } catch (Throwable) {
            return AdministrativeActionLifecycleEnrollmentResult::PersistenceCorrupted;
        }
    }

    public function append(
        AdministrativeActionHistoricalMirrorMutation $mutation,
    ): AdministrativeActionHistoricalMirrorWriteResult {
        try {
            return $this->transaction->run(function () use ($mutation): AdministrativeActionHistoricalMirrorWriteResult {
                $this->lock($mutation->actionId);
                $current = $this->current($mutation->actionId, true);
                $historical = $this->historical($mutation->actionId);
                if ($current === false || $historical === false) {
                    return AdministrativeActionHistoricalMirrorWriteResult::Corrupted;
                }

                try {
                    $stored = $this->mapper->storedState($current);
                } catch (Throwable) {
                    return AdministrativeActionHistoricalMirrorWriteResult::Corrupted;
                }

                $parameters = $this->mapper->transition($mutation);
                if ($stored->version === $mutation->expectedHistoricalVersion + 1
                    && (string) $current['entry_kind'] === 'transition') {
                    $replay = $this->replayResult($current, $parameters);
                    if ($replay !== AdministrativeActionHistoricalMirrorWriteResult::AlreadyApplied) {
                        return $replay;
                    }

                    return $this->historicalReflectsAppliedMutation($historical, $mutation)
                        ? AdministrativeActionHistoricalMirrorWriteResult::AlreadyApplied
                        : AdministrativeActionHistoricalMirrorWriteResult::MirrorDivergence;
                }
                if ($stored->version !== $mutation->expectedHistoricalVersion
                    || (int) $historical['version'] !== $mutation->expectedHistoricalVersion) {
                    return AdministrativeActionHistoricalMirrorWriteResult::VersionConflict;
                }
                if ($stored->state !== $mutation->transition->from
                    || (string) $historical['status'] !== $mutation->transition->from->value) {
                    return AdministrativeActionHistoricalMirrorWriteResult::StateConflict;
                }
                if (! $this->historicalMutationIsCoherent($historical, $mutation)) {
                    return AdministrativeActionHistoricalMirrorWriteResult::MirrorDivergence;
                }

                $this->insert($parameters);
                $this->applyHistoricalMutation($historical, $mutation);

                return AdministrativeActionHistoricalMirrorWriteResult::Applied;
            });
        } catch (Throwable) {
            return AdministrativeActionHistoricalMirrorWriteResult::PersistenceCorrupted;
        }
    }

    public function read(
        AdministrativeActionId $actionId,
    ): AdministrativeActionLifecyclePersistenceReadResult {
        try {
            $row = $this->current($actionId, false);
            if ($row === false) {
                return AdministrativeActionLifecyclePersistenceReadResult::notEnrolled($actionId);
            }

            return AdministrativeActionLifecyclePersistenceReadResult::found($this->mapper->storedState($row));
        } catch (Throwable) {
            return AdministrativeActionLifecyclePersistenceReadResult::corrupted($actionId);
        }
    }

    private function lock(AdministrativeActionId $actionId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:action_id, 0))');
        $statement->execute(['action_id' => $actionId->value]);
    }

    /** @return array<string, mixed>|false */
    private function historical(AdministrativeActionId $actionId): array|false
    {
        $statement = $this->connection->prepare('SELECT id::text, author_id, requires_four_eyes, last_changed_at, status, reason, version FROM administration_audit.administrative_actions WHERE id = :action_id FOR UPDATE');
        $statement->execute(['action_id' => $actionId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    private function current(AdministrativeActionId $actionId, bool $forUpdate): array|false
    {
        $sql = 'SELECT action_id::text, version, entry_kind, previous_state, current_state, action, entry_checksum, source_checksum, mirror_checksum FROM administration_audit.administrative_action_lifecycle_transitions WHERE action_id = :action_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['action_id' => $actionId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, int|string|null> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_lifecycle_transitions (action_id, version, entry_kind, previous_state, current_state, action, entry_checksum, source_checksum, mirror_checksum) VALUES (CAST(:action_id AS uuid), :version, :entry_kind, :previous_state, :current_state, :action, :entry_checksum, :source_checksum, :mirror_checksum)');
        $statement->execute($parameters);
    }

    /** @param array<string, mixed> $current */
    private function sameEnrollment(
        array $current,
        AdministrativeActionLifecycleEnrollmentCheckpoint $checkpoint,
    ): bool {
        return (string) $current['entry_kind'] === 'enrollment'
            && (int) $current['version'] === $checkpoint->historicalVersion
            && (string) $current['current_state'] === $checkpoint->state->value
            && hash_equals((string) $current['source_checksum'], $checkpoint->sourceChecksum->value);
    }

    /** @param array<string, mixed> $current
     * @param  array<string, int|string|null>  $candidate
     */
    private function replayResult(array $current, array $candidate): AdministrativeActionHistoricalMirrorWriteResult
    {
        $sameTransition = (string) $current['previous_state'] === $candidate['previous_state']
            && (string) $current['current_state'] === $candidate['current_state']
            && (string) $current['action'] === $candidate['action'];
        if (! $sameTransition) {
            return AdministrativeActionHistoricalMirrorWriteResult::StateConflict;
        }

        return hash_equals((string) $current['mirror_checksum'], (string) $candidate['mirror_checksum'])
            ? AdministrativeActionHistoricalMirrorWriteResult::AlreadyApplied
            : AdministrativeActionHistoricalMirrorWriteResult::MirrorDivergence;
    }

    /** @param array<string, mixed> $historical */
    private function historicalMutationIsCoherent(
        array $historical,
        AdministrativeActionHistoricalMirrorMutation $mutation,
    ): bool {
        try {
            $lastChangedAt = new DateTimeImmutable((string) $historical['last_changed_at']);
        } catch (Throwable) {
            return false;
        }
        if ($mutation->occurredAt->value < $lastChangedAt) {
            return false;
        }

        return match ($mutation->kind) {
            AdministrativeActionHistoricalMirrorMutationKind::Record => (string) $historical['author_id'] === $mutation->actor->value
                && (string) $historical['reason'] === $mutation->reason->value
                && $this->boolean($historical['requires_four_eyes']) === ($mutation->transition->to === AdministrativeActionLifecycleState::PendingApproval),
            AdministrativeActionHistoricalMirrorMutationKind::Approve,
            AdministrativeActionHistoricalMirrorMutationKind::Reject => (string) $historical['author_id'] !== $mutation->actor->value
                && $this->boolean($historical['requires_four_eyes']),
        };
    }

    /** @param array<string, mixed> $historical */
    private function historicalReflectsAppliedMutation(
        array $historical,
        AdministrativeActionHistoricalMirrorMutation $mutation,
    ): bool {
        if ((int) $historical['version'] !== $mutation->expectedHistoricalVersion + 1
            || (string) $historical['status'] !== $mutation->transition->to->value) {
            return false;
        }

        $audit = $this->connection->prepare('SELECT fact, actor_id, reason, recorded_at FROM administration_audit.administrative_action_audit_entries WHERE action_id = :action_id ORDER BY sequence DESC LIMIT 1');
        $audit->execute(['action_id' => $mutation->actionId->value]);
        $auditRow = $audit->fetch(PDO::FETCH_ASSOC);
        if ($auditRow === false
            || (string) $auditRow['fact'] !== 'administrative_action_'.$mutation->kind->value.'d'
            || (string) $auditRow['actor_id'] !== $mutation->actor->value
            || (string) $auditRow['reason'] !== $mutation->reason->value) {
            return false;
        }

        if ($mutation->kind === AdministrativeActionHistoricalMirrorMutationKind::Record) {
            return true;
        }

        $decision = $this->connection->prepare('SELECT decision_id::text, outcome, decided_by, reason FROM administration_audit.administrative_action_decisions WHERE action_id = :action_id');
        $decision->execute(['action_id' => $mutation->actionId->value]);
        $decisionRow = $decision->fetch(PDO::FETCH_ASSOC);
        if ($decisionRow === false
            || (string) $decisionRow['decision_id'] !== $mutation->decisionId->value
            || (string) $decisionRow['outcome'] !== ($mutation->kind === AdministrativeActionHistoricalMirrorMutationKind::Approve ? 'approved' : 'rejected')
            || (string) $decisionRow['decided_by'] !== $mutation->actor->value
            || (string) $decisionRow['reason'] !== $mutation->reason->value) {
            return false;
        }

        if ($mutation->kind === AdministrativeActionHistoricalMirrorMutationKind::Reject) {
            return true;
        }

        $approval = $this->connection->prepare('SELECT approval_id::text, approver_id FROM administration_audit.administrative_action_approvals WHERE action_id = :action_id');
        $approval->execute(['action_id' => $mutation->actionId->value]);
        $approvalRow = $approval->fetch(PDO::FETCH_ASSOC);

        return $approvalRow !== false
            && (string) $approvalRow['approval_id'] === $mutation->approvalId->value
            && (string) $approvalRow['approver_id'] === $mutation->actor->value;
    }

    /** @param array<string, mixed> $historical */
    private function applyHistoricalMutation(
        array $historical,
        AdministrativeActionHistoricalMirrorMutation $mutation,
    ): void {
        $actionId = $mutation->actionId->value;
        if ($mutation->kind === AdministrativeActionHistoricalMirrorMutationKind::Approve) {
            $approval = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_approvals (action_id, approval_id, approver_id, approved_at) VALUES (:action_id, :approval_id, :actor_id, :occurred_at)');
            $approval->execute([
                'action_id' => $actionId,
                'approval_id' => $mutation->approvalId->value,
                'actor_id' => $mutation->actor->value,
                'occurred_at' => $mutation->occurredAt->canonical(),
            ]);
        }
        if ($mutation->kind !== AdministrativeActionHistoricalMirrorMutationKind::Record) {
            $decision = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_decisions (action_id, decision_id, outcome, decided_by, reason, decided_at) VALUES (:action_id, :decision_id, :outcome, :actor_id, :reason, :occurred_at)');
            $decision->execute([
                'action_id' => $actionId,
                'decision_id' => $mutation->decisionId->value,
                'outcome' => $mutation->kind === AdministrativeActionHistoricalMirrorMutationKind::Approve ? 'approved' : 'rejected',
                'actor_id' => $mutation->actor->value,
                'reason' => $mutation->reason->value,
                'occurred_at' => $mutation->occurredAt->canonical(),
            ]);
        }

        $sequenceStatement = $this->connection->prepare('SELECT count(*) FROM administration_audit.administrative_action_audit_entries WHERE action_id = :action_id');
        $sequenceStatement->execute(['action_id' => $actionId]);
        $sequence = (int) $sequenceStatement->fetchColumn() + 1;
        $audit = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_audit_entries (action_id, sequence, fact, actor_id, reason, recorded_at) VALUES (:action_id, :sequence, :fact, :actor_id, :reason, :occurred_at)');
        $audit->execute([
            'action_id' => $actionId,
            'sequence' => $sequence,
            'fact' => 'administrative_action_'.$mutation->kind->value.'d',
            'actor_id' => $mutation->actor->value,
            'reason' => $mutation->reason->value,
            'occurred_at' => $mutation->occurredAt->canonical(),
        ]);

        $root = $this->connection->prepare('UPDATE administration_audit.administrative_actions SET last_changed_at = :occurred_at, status = :status, version = :version WHERE id = :action_id AND version = :expected_version');
        $root->execute([
            'occurred_at' => $mutation->occurredAt->canonical(),
            'status' => $mutation->transition->to->value,
            'version' => $mutation->expectedHistoricalVersion + 1,
            'action_id' => $actionId,
            'expected_version' => $mutation->expectedHistoricalVersion,
        ]);
        if ($root->rowCount() !== 1) {
            throw new \RuntimeException('Historical mirror version changed during atomic mutation.');
        }
    }

    private function boolean(mixed $value): bool
    {
        return match ($value) {
            true, 1, '1', 't', 'true' => true,
            false, 0, '0', 'f', 'false' => false,
            default => throw new \RuntimeException('Corrupted historical boolean.'),
        };
    }
}
