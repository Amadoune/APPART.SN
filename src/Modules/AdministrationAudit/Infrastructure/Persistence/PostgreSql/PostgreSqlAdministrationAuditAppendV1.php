<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordChecksumV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordIdV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use PDO;
use Throwable;

final readonly class PostgreSqlAdministrationAuditAppendV1 implements AdministrationAuditAppendV1
{
    private const SAVEPOINT = 'administration_audit_public_append_v1';

    private PDO $connection;

    public function __construct(
        AdministrationAuditAppendConnection $connection,
        private AdministrationAuditAppendMapperV1 $mapper,
    ) {
        $this->connection = $connection->pdo();
    }

    public function append(AdministrationAuditRecordV1 $record): AdministrationAuditAppendResultV1
    {
        if (! $this->valid($record)) {
            return AdministrationAuditAppendResultV1::Rejected;
        }

        $ownsTransaction = false;
        try {
            $ownsTransaction = ! $this->connection->inTransaction();
            $ownsTransaction
                ? $this->connection->beginTransaction()
                : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);

            $statement = $this->connection->prepare(
                'INSERT INTO administration_audit.public_append_records
                 (record_id,source_owner,operation,subject_id,actor_id,outcome,correlation_id,
                  causation_id,occurred_at,policy_version,contract_version,record_checksum)
                 VALUES
                 (CAST(:record_id AS uuid),:source_owner,:operation,CAST(:subject_id AS uuid),
                  CAST(:actor_id AS uuid),:outcome,CAST(:correlation_id AS uuid),
                  CAST(:causation_id AS uuid),CAST(:occurred_at AS timestamptz),:policy_version,
                  :contract_version,:record_checksum)
                 ON CONFLICT (record_id) DO NOTHING',
            );
            $statement->execute($this->mapper->parameters($record));
            $result = $statement->rowCount() === 1
                ? AdministrationAuditAppendResultV1::Applied
                : $this->existingResult($record);

            $ownsTransaction
                ? $this->connection->commit()
                : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable) {
            $this->rollback($ownsTransaction);

            return AdministrationAuditAppendResultV1::DependencyUnavailable;
        }
    }

    private function valid(AdministrationAuditRecordV1 $record): bool
    {
        $recordId = AdministrationAuditRecordIdV1::deterministic(
            $record->sourceOwner,
            $record->operation,
            $record->correlationId,
        );
        $checksum = AdministrationAuditRecordChecksumV1::calculate(
            $recordId,
            $record->sourceOwner,
            $record->operation,
            $record->subjectId,
            $record->actorId,
            $record->outcome,
            $record->correlationId,
            $record->causationId,
            $record->occurredAt,
            $record->policyVersion,
        );

        return hash_equals($recordId->value, $record->recordId->value)
            && hash_equals($checksum, $record->checksum);
    }

    private function existingResult(AdministrationAuditRecordV1 $record): AdministrationAuditAppendResultV1
    {
        $statement = $this->connection->prepare(
            'SELECT record_id,source_owner,operation,subject_id,actor_id,outcome,correlation_id,
                    causation_id,occurred_at,policy_version,contract_version,record_checksum
             FROM administration_audit.public_append_records
             WHERE record_id=CAST(:record_id AS uuid)',
        );
        $statement->execute(['record_id' => $record->recordId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (! is_array($row)) {
            return AdministrationAuditAppendResultV1::DependencyUnavailable;
        }

        return $this->mapper->equals($record, $row)
            ? AdministrationAuditAppendResultV1::AlreadyApplied
            : AdministrationAuditAppendResultV1::DivergentRecord;
    }

    private function rollback(bool $ownsTransaction): void
    {
        try {
            if (! $this->connection->inTransaction()) {
                return;
            }
            if ($ownsTransaction) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
        } catch (Throwable) {
            // The public boundary remains fail-closed.
        }
    }
}
