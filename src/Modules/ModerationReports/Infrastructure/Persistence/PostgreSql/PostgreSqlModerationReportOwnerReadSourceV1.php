<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportCaseIdentityV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadResult;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource\ModerationReportOwnerReadMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationReportOwnerReadSourceV1 implements ModerationReportOwnerReadSourceV1
{
    public function __construct(
        private PDO $connection,
        private ModerationReportOwnerReadMapper $mapper,
        private ModerationReportCaseIdentityV1 $identity,
    ) {}

    public function resolve(string $reportId): ModerationReportOwnerReadResult
    {
        if (! ModerationReportOwnerReadMapper::uuid($reportId)) {
            return ModerationReportOwnerReadResult::corrupted();
        }

        $caseId = $this->identity->caseId($reportId);

        try {
            $statement = $this->connection->prepare(
                "SELECT c.case_id,
                        r.report_id,
                        r.payload->>'actorAccountId' AS reporter_account_id
                 FROM moderation_reports.cases c
                 LEFT JOIN LATERAL (
                     SELECT report_id,payload
                     FROM moderation_reports.report_revisions
                     WHERE case_id=c.case_id AND report_id=CAST(:report_id AS uuid)
                     ORDER BY case_version DESC
                     LIMIT 1
                 ) r ON true
                 WHERE c.case_id=CAST(:case_id AS uuid)",
            );
            $statement->execute(['report_id' => $reportId, 'case_id' => $caseId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return ModerationReportOwnerReadResult::missing();
            }
            if ($row['report_id'] === null) {
                return ModerationReportOwnerReadResult::corrupted();
            }

            return ModerationReportOwnerReadResult::found($this->mapper->state($row, $reportId));
        } catch (PDOException) {
            return ModerationReportOwnerReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return ModerationReportOwnerReadResult::corrupted();
        }
    }
}
