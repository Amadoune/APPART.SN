<?php

namespace Tests\PostgreSQL\ModerationReportOwnerReadSource;

use App\Application\ModerationOrchestration\CanonicalModerationCommand;
use App\Application\ModerationReportOwnerReadSource\CanonicalModerationReportCaseIdentityV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadStatus;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource\ModerationReportOwnerReadMapper;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationReportOwnerReadSourceTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function source_resolves_only_the_canonical_case_and_exact_report_without_a_scan(): void
    {
        $reportId = $this->id(1);
        $reporterId = $this->id(2);
        $caseId = CanonicalModerationCommand::deterministicUuid('moderation-case-v1', $reportId);
        $this->insertCase($caseId);
        $this->insertReport($caseId, $reportId, $reporterId, 1);
        $this->insertReport($caseId, $reportId, $reporterId, 2);

        $source = new PostgreSqlModerationReportOwnerReadSourceV1(
            $this->connection,
            new ModerationReportOwnerReadMapper,
            new CanonicalModerationReportCaseIdentityV1,
        );
        $result = $source->resolve($reportId);

        self::assertSame(ModerationReportOwnerReadStatus::Found, $result->status);
        self::assertSame($caseId, $result->state?->caseId);
        self::assertSame($reportId, $result->state?->reportId);
        self::assertSame($reporterId, $result->state?->reporterAccountId);
        self::assertSame(ModerationReportOwnerReadStatus::Missing, $source->resolve($this->id(3))->status);
        self::assertSame(ModerationReportOwnerReadStatus::Corrupted, $source->resolve('invalid')->status);
    }

    #[Test]
    public function canonical_case_without_the_exact_report_is_corrupted(): void
    {
        $reportId = $this->id(4);
        $caseId = CanonicalModerationCommand::deterministicUuid('moderation-case-v1', $reportId);
        $this->insertCase($caseId);

        $source = new PostgreSqlModerationReportOwnerReadSourceV1(
            $this->connection,
            new ModerationReportOwnerReadMapper,
            new CanonicalModerationReportCaseIdentityV1,
        );

        self::assertSame(ModerationReportOwnerReadStatus::Corrupted, $source->resolve($reportId)->status);
    }

    private function insertCase(string $caseId): void
    {
        $statement = $this->connection->prepare(
            "INSERT INTO moderation_reports.cases
             (case_id,target_type,target_id,status,current_decision_id,version,last_intent_id,last_intent_checksum,updated_at)
             VALUES(CAST(:case_id AS uuid),'Listing',CAST(:target_id AS uuid),'Open',NULL,2,
                    CAST(:intent_id AS uuid),:checksum,'2026-07-30T10:00:00+00:00')",
        );
        $statement->execute([
            'case_id' => $caseId,
            'target_id' => $this->id(8),
            'intent_id' => $this->id(9),
            'checksum' => str_repeat('a', 64),
        ]);
    }

    private function insertReport(string $caseId, string $reportId, string $reporterId, int $version): void
    {
        $payload = json_encode((object) [
            'actorAccountId' => $reporterId,
            'category' => 'fraud',
            'disposition' => null,
        ], JSON_THROW_ON_ERROR);
        $statement = $this->connection->prepare(
            "INSERT INTO moderation_reports.report_revisions
             (case_id,report_id,case_version,payload,payload_checksum,recorded_at)
             VALUES(CAST(:case_id AS uuid),CAST(:report_id AS uuid),:version,CAST(:payload AS jsonb),
                    :checksum,'2026-07-30T10:00:00+00:00')",
        );
        $statement->execute([
            'case_id' => $caseId,
            'report_id' => $reportId,
            'version' => $version,
            'payload' => $payload,
            'checksum' => hash('sha256', $payload),
        ]);
    }

    private function id(int $suffix): string
    {
        return sprintf('53a20000-0000-4000-8000-%012d', $suffix);
    }
}
