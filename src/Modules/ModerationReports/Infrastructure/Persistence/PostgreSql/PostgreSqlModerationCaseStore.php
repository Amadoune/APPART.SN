<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceReadResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationCaseStore implements ModerationCaseStore
{
    public function __construct(
        private PDO $connection,
        private ModerationPersistenceMapper $mapper,
    ) {}

    public function read(string $caseId): ModerationCasePersistenceReadResult
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT case_id,target_type,target_id,status,current_decision_id,version,last_intent_id,last_intent_checksum,updated_at
                 FROM moderation_reports.cases WHERE case_id=CAST(:case_id AS uuid)',
            );
            $statement->execute(['case_id' => $caseId]);
            $root = $statement->fetch(PDO::FETCH_ASSOC);
            if ($root === false) {
                return ModerationCasePersistenceReadResult::missing();
            }

            return ModerationCasePersistenceReadResult::found($this->mapper->state(
                $root,
                $this->latest('report_revisions', 'report_id', $caseId),
                $this->latest('finding_revisions', 'finding_id', $caseId),
                $this->latest('decision_revisions', 'decision_id', $caseId),
            ));
        } catch (PDOException) {
            return ModerationCasePersistenceReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return ModerationCasePersistenceReadResult::corrupted();
        }
    }

    public function save(
        ModerationCasePersistenceState $candidate,
        int $expectedVersion,
    ): ModerationPersistenceWriteResult {
        try {
            return $this->transaction(function () use ($candidate, $expectedVersion): ModerationPersistenceWriteResult {
                $this->lock($candidate->caseId);
                $intent = $this->intent($candidate->caseId, $candidate->intentId, $candidate->intentChecksum);
                if ($intent !== null) {
                    return $intent;
                }
                $version = $this->version($candidate->caseId);
                if ($version !== $expectedVersion || $candidate->version !== $expectedVersion + 1) {
                    return ModerationPersistenceWriteResult::VersionConflict;
                }

                $parameters = $this->mapper->caseParameters($candidate);
                $statement = $this->connection->prepare(
                    'INSERT INTO moderation_reports.cases(case_id,target_type,target_id,status,current_decision_id,version,last_intent_id,last_intent_checksum,updated_at)
                     VALUES(CAST(:case_id AS uuid),:target_type,CAST(:target_id AS uuid),:status,CAST(:current_decision_id AS uuid),:version,CAST(:intent_id AS uuid),:intent_checksum,CAST(:updated_at AS timestamptz))
                     ON CONFLICT(case_id) DO UPDATE SET
                        status=EXCLUDED.status,current_decision_id=EXCLUDED.current_decision_id,version=EXCLUDED.version,
                        last_intent_id=EXCLUDED.last_intent_id,last_intent_checksum=EXCLUDED.last_intent_checksum,updated_at=EXCLUDED.updated_at
                     WHERE moderation_reports.cases.target_type=EXCLUDED.target_type
                       AND moderation_reports.cases.target_id=EXCLUDED.target_id',
                );
                $statement->execute($parameters);
                if ($statement->rowCount() !== 1) {
                    return ModerationPersistenceWriteResult::IdentityConflict;
                }
                $this->appendRecords('report_revisions', 'report_id', $candidate->caseId, $candidate->reports, $candidate->version);
                $this->appendRecords('finding_revisions', 'finding_id', $candidate->caseId, $candidate->findings, $candidate->version);
                $this->appendRecords('decision_revisions', 'decision_id', $candidate->caseId, $candidate->decisions, $candidate->version);
                $this->appendSupersessions($candidate);

                $intentStatement = $this->connection->prepare(
                    'INSERT INTO moderation_reports.case_intents(case_id,intent_id,intent_checksum,result_version,recorded_at)
                     VALUES(CAST(:case_id AS uuid),CAST(:intent_id AS uuid),:intent_checksum,:result_version,CAST(:recorded_at AS timestamptz))',
                );
                $intentStatement->execute([
                    'case_id' => $candidate->caseId,
                    'intent_id' => $candidate->intentId,
                    'intent_checksum' => $candidate->intentChecksum,
                    'result_version' => $candidate->version,
                    'recorded_at' => $candidate->updatedAt->format('Y-m-d H:i:s.uP'),
                ]);

                return ModerationPersistenceWriteResult::Applied;
            });
        } catch (PDOException $error) {
            return $error->getCode() === '23505'
                ? ModerationPersistenceWriteResult::IdentityConflict
                : ModerationPersistenceWriteResult::Rejected;
        }
    }

    /** @return list<array<string, mixed>> */
    private function latest(string $table, string $identityColumn, string $caseId): array
    {
        $statement = $this->connection->prepare(
            "SELECT DISTINCT ON ({$identityColumn}) {$identityColumn},payload::text AS payload,recorded_at
             FROM moderation_reports.{$table}
             WHERE case_id=CAST(:case_id AS uuid)
             ORDER BY {$identityColumn},case_version DESC",
        );
        $statement->execute(['case_id' => $caseId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param list<ModerationPersistenceRecord> $records */
    private function appendRecords(
        string $table,
        string $identityColumn,
        string $caseId,
        array $records,
        int $version,
    ): void {
        $statement = $this->connection->prepare(
            "INSERT INTO moderation_reports.{$table}(case_id,{$identityColumn},case_version,payload,payload_checksum,recorded_at)
             VALUES(CAST(:case_id AS uuid),CAST(:{$identityColumn} AS uuid),:case_version,CAST(:payload AS jsonb),:payload_checksum,CAST(:recorded_at AS timestamptz))",
        );
        foreach ($records as $record) {
            $statement->execute($this->mapper->recordParameters($caseId, $identityColumn, $record, $version));
        }
    }

    private function appendSupersessions(ModerationCasePersistenceState $candidate): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO moderation_reports.decision_supersessions(case_id,decision_id,superseded_decision_id,case_version,recorded_at)
             VALUES(CAST(:case_id AS uuid),CAST(:decision_id AS uuid),CAST(:superseded_decision_id AS uuid),:case_version,CAST(:recorded_at AS timestamptz))
             ON CONFLICT(case_id,decision_id) DO NOTHING',
        );
        foreach ($candidate->decisions as $decision) {
            $superseded = $decision->payload['supersededDecisionId'] ?? null;
            if (! is_string($superseded) || $superseded === '') {
                continue;
            }
            $statement->execute([
                'case_id' => $candidate->caseId,
                'decision_id' => $decision->id,
                'superseded_decision_id' => $superseded,
                'case_version' => $candidate->version,
                'recorded_at' => $decision->recordedAt->format('Y-m-d H:i:s.uP'),
            ]);
        }
    }

    private function lock(string $caseId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:case_id,0))');
        $statement->execute(['case_id' => $caseId]);
    }

    private function version(string $caseId): int
    {
        $statement = $this->connection->prepare(
            'SELECT version FROM moderation_reports.cases WHERE case_id=CAST(:case_id AS uuid)',
        );
        $statement->execute(['case_id' => $caseId]);
        $version = $statement->fetchColumn();

        return $version === false ? 0 : (int) $version;
    }

    private function intent(string $caseId, string $intentId, string $checksum): ?ModerationPersistenceWriteResult
    {
        $statement = $this->connection->prepare(
            'SELECT intent_checksum FROM moderation_reports.case_intents
             WHERE case_id=CAST(:case_id AS uuid) AND intent_id=CAST(:intent_id AS uuid)',
        );
        $statement->execute(['case_id' => $caseId, 'intent_id' => $intentId]);
        $stored = $statement->fetchColumn();
        if ($stored === false) {
            return null;
        }

        return hash_equals((string) $stored, $checksum)
            ? ModerationPersistenceWriteResult::AlreadyApplied
            : ModerationPersistenceWriteResult::DivergentIntent;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    private function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'moderation_case_store';
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec("SAVEPOINT {$savepoint}");
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            } else {
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            }
            throw $error;
        }
    }
}
