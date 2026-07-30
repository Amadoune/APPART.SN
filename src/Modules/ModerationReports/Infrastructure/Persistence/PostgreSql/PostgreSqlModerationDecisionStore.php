<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationDecisionPersistenceReadResult;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationDecisionStore implements ModerationDecisionStore
{
    public function __construct(
        private PDO $connection,
        private ModerationPersistenceMapper $mapper,
    ) {}

    public function read(string $caseId, string $decisionId): ModerationDecisionPersistenceReadResult
    {
        return $this->query(
            'SELECT decision_id,payload::text AS payload,recorded_at
             FROM moderation_reports.decision_revisions
             WHERE case_id=CAST(:case_id AS uuid) AND decision_id=CAST(:decision_id AS uuid)
             ORDER BY case_version DESC LIMIT 1',
            ['case_id' => $caseId, 'decision_id' => $decisionId],
        );
    }

    public function readCurrent(string $caseId): ModerationDecisionPersistenceReadResult
    {
        return $this->query(
            'SELECT d.decision_id,d.payload::text AS payload,d.recorded_at
             FROM moderation_reports.cases c
             JOIN LATERAL (
                 SELECT decision_id,payload,recorded_at
                 FROM moderation_reports.decision_revisions
                 WHERE case_id=c.case_id AND decision_id=c.current_decision_id
                 ORDER BY case_version DESC LIMIT 1
             ) d ON true
             WHERE c.case_id=CAST(:case_id AS uuid)',
            ['case_id' => $caseId],
        );
    }

    /** @param array<string, string> $parameters */
    private function query(string $sql, array $parameters): ModerationDecisionPersistenceReadResult
    {
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return ModerationDecisionPersistenceReadResult::missing();
            }

            return ModerationDecisionPersistenceReadResult::found($this->mapper->decision($row));
        } catch (PDOException) {
            return ModerationDecisionPersistenceReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return ModerationDecisionPersistenceReadResult::corrupted();
        }
    }
}
