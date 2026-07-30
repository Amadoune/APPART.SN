<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionSnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransaction;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\ApprovalSnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AuditEntrySnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\DecisionSnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PersistentAdministrativeActionIntegrity;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlAdministrativeActionRepository implements AdministrativeActionRegistry
{
    private AdministrativeActionTransaction $transaction;

    public function __construct(
        private PDO $connection,
        private AdministrativeActionMapper $mapper,
        ?AdministrativeActionTransaction $transaction = null,
    ) {
        $this->transaction = $transaction ?? new PostgreSqlAdministrativeActionTransaction($connection);
    }

    public function find(AdministrativeActionId $id): ?AdministrativeAction
    {
        $statement = $this->connection->prepare('SELECT id, author_id, target_id, action_type, requires_four_eyes, last_changed_at, status, reason, version FROM administration_audit.administrative_actions WHERE id = :id');
        $statement->execute(['id' => $id->value]);
        $root = $statement->fetch(PDO::FETCH_ASSOC);
        if ($root === false) {
            return null;
        }

        return $this->mapper->toAggregate($this->snapshotFromRows($root));
    }

    public function add(AdministrativeAction $action): void
    {
        $snapshot = $this->mapper->toSnapshot($action);
        try {
            $this->transaction->run(function () use ($snapshot): void {
                $this->insertRoot($snapshot);
                $this->insertChildren($snapshot);
            });
        } catch (PDOException $error) {
            if ($error->getCode() === '23505') {
                throw new AdministrativeActionIdentityConflict;
            }

            throw PersistentAdministrativeActionIntegrity::invalid('write');
        }
    }

    public function save(AdministrativeAction $action, int $expectedVersion): void
    {
        $snapshot = $this->mapper->toSnapshot($action);
        if ($snapshot->version <= $expectedVersion) {
            throw new ConcurrentAdministrativeActionModification;
        }

        try {
            $this->transaction->run(function () use ($snapshot, $expectedVersion): void {
                $statement = $this->connection->prepare('UPDATE administration_audit.administrative_actions SET author_id = :author_id, target_id = :target_id, action_type = :action_type, requires_four_eyes = :requires_four_eyes, last_changed_at = :last_changed_at, status = :status, reason = :reason, version = :version WHERE id = :id AND version = :expected_version');
                $statement->execute($this->rootParameters($snapshot) + ['expected_version' => $expectedVersion]);
                if ($statement->rowCount() !== 1) {
                    throw new ConcurrentAdministrativeActionModification;
                }

                $this->appendChildren($snapshot);
            });
        } catch (ConcurrentAdministrativeActionModification $error) {
            throw $error;
        } catch (Throwable $error) {
            throw PersistentAdministrativeActionIntegrity::invalid('write');
        }
    }

    private function insertRoot(AdministrativeActionSnapshot $snapshot): void
    {
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_actions (id, author_id, target_id, action_type, requires_four_eyes, last_changed_at, status, reason, version) VALUES (:id, :author_id, :target_id, :action_type, :requires_four_eyes, :last_changed_at, :status, :reason, :version)');
        $statement->execute($this->rootParameters($snapshot));
    }

    /** @return array<string, int|string|bool|null> */
    private function rootParameters(AdministrativeActionSnapshot $snapshot): array
    {
        return [
            'id' => $snapshot->id,
            'author_id' => $snapshot->authorId,
            'target_id' => $snapshot->targetId,
            'action_type' => $snapshot->actionType,
            'requires_four_eyes' => $snapshot->requiresFourEyes,
            'last_changed_at' => $snapshot->lastChangedAt,
            'status' => $snapshot->status,
            'reason' => $snapshot->reason,
            'version' => $snapshot->version,
        ];
    }

    private function insertChildren(AdministrativeActionSnapshot $snapshot): void
    {
        $this->insertApproval($snapshot->id, $snapshot->approval);
        $this->insertDecision($snapshot->id, $snapshot->decision);
        foreach ($snapshot->auditEntries as $entry) {
            $this->insertAuditEntry($snapshot->id, $entry);
        }
    }

    private function appendChildren(AdministrativeActionSnapshot $snapshot): void
    {
        $current = $this->snapshotFromRows($this->rootRow($snapshot->id));
        if (count($current->auditEntries) > count($snapshot->auditEntries)) {
            throw PersistentAdministrativeActionIntegrity::invalid('audit history regression');
        }
        foreach ($current->auditEntries as $index => $entry) {
            if ($entry != $snapshot->auditEntries[$index]) {
                throw PersistentAdministrativeActionIntegrity::invalid('audit history rewrite');
            }
        }
        $this->assertStableChild($current->approval, $snapshot->approval, 'approval');
        $this->assertStableChild($current->decision, $snapshot->decision, 'decision');
        if ($current->approval === null) {
            $this->insertApproval($snapshot->id, $snapshot->approval);
        }
        if ($current->decision === null) {
            $this->insertDecision($snapshot->id, $snapshot->decision);
        }
        foreach (array_slice($snapshot->auditEntries, count($current->auditEntries)) as $entry) {
            $this->insertAuditEntry($snapshot->id, $entry);
        }
    }

    private function insertApproval(string $actionId, ?ApprovalSnapshot $approval): void
    {
        if ($approval === null) {
            return;
        }
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_approvals (action_id, approval_id, approver_id, approved_at) VALUES (:action_id, :approval_id, :approver_id, :approved_at)');
        $statement->execute(['action_id' => $actionId, 'approval_id' => $approval->id, 'approver_id' => $approval->approverId, 'approved_at' => $approval->approvedAt]);
    }

    private function insertDecision(string $actionId, ?DecisionSnapshot $decision): void
    {
        if ($decision === null) {
            return;
        }
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_decisions (action_id, decision_id, outcome, decided_by, reason, decided_at) VALUES (:action_id, :decision_id, :outcome, :decided_by, :reason, :decided_at)');
        $statement->execute(['action_id' => $actionId, 'decision_id' => $decision->id, 'outcome' => $decision->outcome, 'decided_by' => $decision->decidedBy, 'reason' => $decision->reason, 'decided_at' => $decision->decidedAt]);
    }

    private function insertAuditEntry(string $actionId, AuditEntrySnapshot $entry): void
    {
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_audit_entries (action_id, sequence, fact, actor_id, reason, recorded_at) VALUES (:action_id, :sequence, :fact, :actor_id, :reason, :recorded_at)');
        $statement->execute(['action_id' => $actionId, 'sequence' => $entry->sequence, 'fact' => $entry->fact, 'actor_id' => $entry->actorId, 'reason' => $entry->reason, 'recorded_at' => $entry->recordedAt]);
    }

    /** @param array<string, mixed> $root */
    private function snapshotFromRows(array $root): AdministrativeActionSnapshot
    {
        $id = (string) $root['id'];
        $approvalStatement = $this->connection->prepare('SELECT approval_id, approver_id, approved_at FROM administration_audit.administrative_action_approvals WHERE action_id = :id');
        $approvalStatement->execute(['id' => $id]);
        $approvalRow = $approvalStatement->fetch(PDO::FETCH_ASSOC);
        $decisionStatement = $this->connection->prepare('SELECT decision_id, outcome, decided_by, reason, decided_at FROM administration_audit.administrative_action_decisions WHERE action_id = :id');
        $decisionStatement->execute(['id' => $id]);
        $decisionRow = $decisionStatement->fetch(PDO::FETCH_ASSOC);
        $auditStatement = $this->connection->prepare('SELECT sequence, fact, actor_id, reason, recorded_at FROM administration_audit.administrative_action_audit_entries WHERE action_id = :id ORDER BY sequence');
        $auditStatement->execute(['id' => $id]);
        $auditRows = $auditStatement->fetchAll(PDO::FETCH_ASSOC);

        return new AdministrativeActionSnapshot(
            $id,
            (string) $root['author_id'],
            (string) $root['target_id'],
            (string) $root['action_type'],
            $this->boolean($root['requires_four_eyes']),
            $this->normalizeDate((string) $root['last_changed_at']),
            (string) $root['status'],
            $root['reason'] === null ? null : (string) $root['reason'],
            $approvalRow === false ? null : new ApprovalSnapshot((string) $approvalRow['approval_id'], (string) $approvalRow['approver_id'], $this->normalizeDate((string) $approvalRow['approved_at'])),
            $decisionRow === false ? null : new DecisionSnapshot((string) $decisionRow['decision_id'], (string) $decisionRow['outcome'], (string) $decisionRow['decided_by'], (string) $decisionRow['reason'], $this->normalizeDate((string) $decisionRow['decided_at'])),
            array_map(fn (array $row): AuditEntrySnapshot => new AuditEntrySnapshot((int) $row['sequence'], (string) $row['fact'], (string) $row['actor_id'], (string) $row['reason'], $this->normalizeDate((string) $row['recorded_at'])), $auditRows),
            (int) $root['version'],
        );
    }

    /** @return array<string, mixed> */
    private function rootRow(string $id): array
    {
        $statement = $this->connection->prepare('SELECT id, author_id, target_id, action_type, requires_four_eyes, last_changed_at, status, reason, version FROM administration_audit.administrative_actions WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw PersistentAdministrativeActionIntegrity::invalid('missing root');
        }

        return $row;
    }

    private function assertStableChild(?object $current, ?object $candidate, string $component): void
    {
        if ($current !== null && $current != $candidate) {
            throw PersistentAdministrativeActionIntegrity::invalid($component.' rewrite');
        }
    }

    private function normalizeDate(string $value): string
    {
        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d\\TH:i:s.uP');
        } catch (Throwable) {
            throw PersistentAdministrativeActionIntegrity::invalid('date');
        }
    }

    private function boolean(mixed $value): bool
    {
        return match ($value) {
            true, 1, '1', 't', 'true' => true,
            false, 0, '0', 'f', 'false' => false,
            default => throw PersistentAdministrativeActionIntegrity::invalid('boolean'),
        };
    }
}
