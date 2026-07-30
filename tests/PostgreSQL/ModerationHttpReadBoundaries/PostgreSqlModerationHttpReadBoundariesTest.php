<?php

namespace Tests\PostgreSQL\ModerationHttpReadBoundaries;

use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationCaseV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationDecisionV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationQueueV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadOwnModerationReportV1;
use App\Application\ModerationOrchestration\CanonicalModerationCommand;
use App\Application\ModerationReportOwnerReadSource\CanonicalModerationReportCaseIdentityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationCaseViewLevelV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationDecisionPurposeV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorCodecV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueReadMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource\ModerationReportOwnerReadMapper;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationCaseViewMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationDecisionViewMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\OwnModerationReportViewMapperV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationHttpReadBoundariesTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_four_boundaries_read_only_owner_sources_end_to_end(): void
    {
        $reportId = $this->id(1);
        $accountId = $this->id(2);
        $decisionId = $this->id(3);
        $caseId = CanonicalModerationCommand::deterministicUuid('moderation-case-v1', $reportId);
        $this->seed($caseId, $reportId, $accountId, $decisionId);

        $mapper = new ModerationPersistenceMapper;
        $cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $decisions = new PostgreSqlModerationDecisionStore($this->connection, $mapper);
        $authorization = new class implements ModeratorAuthorizationReaderV1
        {
            public function authorize(
                AccountId $accountId,
                ModerationCapabilityV1 $capability,
                DateTimeImmutable $observedAt,
            ): ModeratorAuthorizationDecisionV1 {
                return ModeratorAuthorizationDecisionV1::Allowed;
            }
        };

        $reportReader = new OwnerReadOwnModerationReportV1(
            new PostgreSqlModerationReportOwnerReadSourceV1(
                $this->connection,
                new ModerationReportOwnerReadMapper,
                new CanonicalModerationReportCaseIdentityV1,
            ),
            $cases,
            new OwnModerationReportViewMapperV1,
        );
        $queueReader = new OwnerReadModerationQueueV1(
            $authorization,
            new PostgreSqlModerationQueueOwnerReadSourceV1(
                $this->connection,
                new ModerationQueueReadMapperV1,
                new ModerationQueueCursorCodecV1('test-key'),
            ),
        );
        $caseReader = new OwnerReadModerationCaseV1(
            $authorization,
            $cases,
            new ModerationCaseViewMapperV1,
        );
        $decisionReader = new OwnerReadModerationDecisionV1(
            $authorization,
            $decisions,
            new ModerationDecisionViewMapperV1,
        );
        $at = new DateTimeImmutable('2026-07-30T10:00:00+00:00');

        self::assertSame(
            ReadStatusV1::Visible,
            $reportReader->read(new ReadOwnModerationReportQueryV1($reportId, $accountId))->status,
        );
        self::assertSame(
            ReadStatusV1::Available,
            $queueReader->read(new ReadModerationQueueQueryV1(
                $accountId,
                $at,
                new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available, 'fraud'),
                null,
                10,
            ))->status,
        );
        self::assertSame(
            ReadStatusV1::Found,
            $caseReader->read(new ReadModerationCaseQueryV1(
                $caseId,
                $accountId,
                $at,
                ModerationCaseViewLevelV1::Investigate,
            ))->status,
        );
        $decision = $decisionReader->read(new ReadModerationDecisionQueryV1(
            $caseId,
            $decisionId,
            $accountId,
            $at,
            ModerationDecisionPurposeV1::Audit,
        ));
        self::assertSame(ReadStatusV1::Found, $decision->status);
        self::assertSame('Suspend', $decision->decision?->attributes['disposition']);
        self::assertSame(1, (int) $this->connection->query(
            'SELECT count(*) FROM moderation_reports.queue_items',
        )->fetchColumn());
    }

    private function seed(string $caseId, string $reportId, string $accountId, string $decisionId): void
    {
        $this->connection->prepare(
            "INSERT INTO moderation_reports.cases
             (case_id,target_type,target_id,status,current_decision_id,version,last_intent_id,last_intent_checksum,updated_at)
             VALUES(CAST(:case_id AS uuid),'Listing',CAST(:target_id AS uuid),'Decided',
                    CAST(:decision_id AS uuid),1,CAST(:intent_id AS uuid),:checksum,:at)",
        )->execute([
            'case_id' => $caseId,
            'target_id' => $this->id(4),
            'decision_id' => $decisionId,
            'intent_id' => $this->id(5),
            'checksum' => str_repeat('a', 64),
            'at' => '2026-07-30T10:00:00+00:00',
        ]);
        $this->insertRevision('report_revisions', 'report_id', $caseId, $reportId, [
            'actorAccountId' => $accountId,
            'category' => 'fraud',
        ]);
        $this->insertRevision('decision_revisions', 'decision_id', $caseId, $decisionId, [
            'disposition' => 'Suspend',
            'policyVersion' => 'v1',
        ]);
        $this->connection->prepare(
            "INSERT INTO moderation_reports.queue_items
             (queue_item_id,case_id,priority,category,state,lease_id,claim_owner_id,lease_expires_at,source_version,updated_at)
             VALUES(CAST(:queue_id AS uuid),CAST(:case_id AS uuid),90,'fraud','Available',NULL,NULL,NULL,1,:at)",
        )->execute([
            'queue_id' => $this->id(6),
            'case_id' => $caseId,
            'at' => '2026-07-30T10:00:00+00:00',
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function insertRevision(
        string $table,
        string $identityColumn,
        string $caseId,
        string $identity,
        array $payload,
    ): void {
        $statement = $this->connection->prepare(
            "INSERT INTO moderation_reports.{$table}
             (case_id,{$identityColumn},case_version,payload,payload_checksum,recorded_at)
             VALUES(CAST(:case_id AS uuid),CAST(:identity AS uuid),1,CAST(:payload AS jsonb),:checksum,:at)",
        );
        $statement->execute([
            'case_id' => $caseId,
            'identity' => $identity,
            'payload' => json_encode((object) $payload, JSON_THROW_ON_ERROR),
            'checksum' => str_repeat('b', 64),
            'at' => '2026-07-30T10:00:00+00:00',
        ]);
    }

    private function id(int $suffix): string
    {
        return sprintf('00000000-0000-4000-8000-%012d', $suffix);
    }
}
