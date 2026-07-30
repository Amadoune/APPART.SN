<?php

namespace Tests\PostgreSQL\ModerationHttp;

use App\Application\ModerationHttp\DeterministicModerationHttpRuntimeV1;
use App\Application\ModerationHttp\ModerationHttpOperation;
use App\Application\ModerationHttp\ModerationHttpStatus;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationCaseV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationQueueV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadOwnModerationReportV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationHttpMutationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationCaseStore $cases;

    private PostgreSqlModerationQueueStore $queue;

    private DeterministicModerationHttpRuntimeV1 $http;

    private RecordingModeratorAuthorization $authorization;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $runtime = new DeterministicModerationRuntimeV1(
            $this->cases,
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            new DeterministicModerationQueueRuntimeV1($this->queue),
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        );
        $this->authorization = new RecordingModeratorAuthorization;
        $this->http = new DeterministicModerationHttpRuntimeV1(
            new DeterministicModerationCaseOrchestratorV1($runtime),
            $this->createStub(ReadOwnModerationReportV1::class),
            $this->createStub(ReadModerationQueueV1::class),
            $this->createStub(ReadModerationCaseV1::class),
            $this->createStub(ReadModerationDecisionV1::class),
            $this->authorization,
        );
    }

    public function test_six_http_mutations_preserve_idempotence_four_eyes_and_optimistic_locking(): void
    {
        $reportId = $this->id(100);
        $common = ['occurredAt' => $this->time(), 'policyVersion' => 'v1'];
        $submitted = $this->http->execute(
            ModerationHttpOperation::SubmitReport,
            $this->id(1),
            $reportId,
            $this->id(10),
            $common + ['targetType' => 'Listing', 'targetId' => $this->id(900), 'category' => 'fraud', 'statementReference' => 'opaque'],
        );
        self::assertSame(ModerationHttpStatus::Created, $submitted->status);
        $caseId = (string) $submitted->data['caseId'];
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::SubmitReport,
            $this->id(1),
            $reportId,
            $this->id(10),
            $common + ['targetType' => 'Listing', 'targetId' => $this->id(900), 'category' => 'fraud', 'statementReference' => 'opaque'],
        )->status);

        $validation = $common + ['caseId' => $caseId, 'disposition' => 'Accepted', 'reasonCode' => 'verified', 'expectedVersion' => 1];
        self::assertSame(ModerationHttpStatus::Forbidden, $this->http->execute(
            ModerationHttpOperation::ValidateReport, $this->id(1), $reportId, $this->id(11), $validation,
        )->status);
        self::assertSame(1, $this->cases->read($caseId)->state?->version);
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::ValidateReport, $this->id(2), $reportId, $this->id(12), $validation,
        )->status);
        self::assertSame(ModerationHttpStatus::Conflict, $this->http->execute(
            ModerationHttpOperation::ValidateReport, $this->id(3), $reportId, $this->id(13), $validation,
        )->status);

        $finding = $common + [
            'findingId' => $this->id(200),
            'reportIds' => [$reportId],
            'findingCode' => 'confirmed',
            'evidenceReferences' => ['opaque'],
            'expectedVersion' => 2,
        ];
        self::assertSame(ModerationHttpStatus::Forbidden, $this->http->execute(
            ModerationHttpOperation::RecordFinding, $this->id(2), $caseId, $this->id(14), $finding,
        )->status);
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::RecordFinding, $this->id(3), $caseId, $this->id(15), $finding,
        )->status);

        $decision = $common + [
            'decisionId' => $this->id(300),
            'findingIds' => [$this->id(200)],
            'disposition' => 'Confirmed',
            'targetAction' => 'None',
            'expectedVersion' => 3,
        ];
        self::assertSame(ModerationHttpStatus::Forbidden, $this->http->execute(
            ModerationHttpOperation::IssueDecision, $this->id(1), $caseId, $this->id(16), $decision,
        )->status);
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::IssueDecision, $this->id(4), $caseId, $this->id(17), $decision,
        )->status);
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::CloseCase,
            $this->id(5),
            $caseId,
            $this->id(18),
            $common + ['closureCode' => 'resolved', 'expectedVersion' => 4],
        )->status);

        $item = new ModerationQueueItemState(
            $this->id(400), $caseId, 80, 'fraud', 'Available', null, null, null, 5,
            new DateTimeImmutable($this->time()),
        );
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        $claim = $common + ['leaseId' => $this->id(402), 'leaseExpiresAt' => '2026-07-30T10:05:00+00:00'];
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::ClaimQueueItem, $this->id(6), $item->queueItemId, $this->id(401), $claim,
        )->status);
        self::assertSame(ModerationHttpStatus::Succeeded, $this->http->execute(
            ModerationHttpOperation::ClaimQueueItem, $this->id(6), $item->queueItemId, $this->id(401), $claim,
        )->status);
        self::assertSame([
            'report',
            'report',
            'validate',
            'validate',
            'validate',
            'investigate',
            'investigate',
            'decide',
            'decide',
            'decide',
            'investigate',
            'investigate',
        ], array_map(
            static fn (ModerationCapabilityV1 $capability): string => $capability->value,
            $this->authorization->capabilities,
        ));
    }

    public function test_outer_rollback_leaves_no_partial_http_mutation(): void
    {
        $reportId = $this->id(500);
        $this->connection->beginTransaction();
        $result = $this->http->execute(
            ModerationHttpOperation::SubmitReport,
            $this->id(7),
            $reportId,
            $this->id(501),
            [
                'occurredAt' => $this->time(),
                'policyVersion' => 'v1',
                'targetType' => 'Listing',
                'targetId' => $this->id(900),
                'category' => 'fraud',
                'statementReference' => 'opaque',
            ],
        );
        self::assertSame(ModerationHttpStatus::Created, $result->status);
        $this->connection->rollBack();
        self::assertSame(
            ModerationPersistenceReadStatus::Missing,
            $this->cases->read((string) $result->data['caseId'])->status,
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('53fa0000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): string
    {
        return '2026-07-30T10:00:00+00:00';
    }
}

final class RecordingModeratorAuthorization implements ModeratorAuthorizationReaderV1
{
    /** @var list<ModerationCapabilityV1> */
    public array $capabilities = [];

    public function authorize(
        AccountId $accountId,
        ModerationCapabilityV1 $capability,
        DateTimeImmutable $observedAt,
    ): ModeratorAuthorizationDecisionV1 {
        $this->capabilities[] = $capability;

        return ModeratorAuthorizationDecisionV1::Allowed;
    }
}
