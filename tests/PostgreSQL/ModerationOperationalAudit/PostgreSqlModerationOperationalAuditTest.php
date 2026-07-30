<?php

namespace Tests\PostgreSQL\ModerationOperationalAudit;

use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditConsumer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditStatus;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ModerationOperationalAuditTest;

final class PostgreSqlModerationOperationalAuditTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationOutbox $outbox;

    private PostgreSqlModerationDecisionStore $decisions;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->decisions = new PostgreSqlModerationDecisionStore($this->connection, $mapper);
        $this->outbox = new PostgreSqlModerationOutbox(
            $this->connection,
            new ModerationEventTransportSerializer,
            new DeterministicModerationEventRouter,
        );
        self::assertSame(
            ModerationPersistenceWriteResult::Applied,
            $cases->save(new ModerationCasePersistenceState(
                ModerationOperationalAuditTest::id(1),
                'Listing',
                ModerationOperationalAuditTest::id(9),
                'Decided',
                ModerationOperationalAuditTest::id(2),
                1,
                ModerationOperationalAuditTest::id(8),
                str_repeat('a', 64),
                ModerationOperationalAuditTest::now(),
                [],
                [],
                [new ModerationPersistenceRecord(
                    ModerationOperationalAuditTest::id(2),
                    ['actorAccountId' => ModerationOperationalAuditTest::id(3)],
                    ModerationOperationalAuditTest::now(),
                )],
            ), 0),
        );
        $this->outbox->append(new ModerationDeliveryMessageV1(ModerationOperationalAuditTest::event()));
    }

    #[Test]
    public function audit_append_replay_and_interruption_recovery_are_idempotent_across_owner_transactions(): void
    {
        $auditConnection = PostgreSqlTestEnvironment::connection();
        $consumer = $this->consumer(new PostgreSqlAdministrationAuditAppendV1(
            new AdministrationAuditAppendConnection($auditConnection),
            new AdministrationAuditAppendMapperV1,
        ));

        self::assertSame(
            ModerationOperationalAuditStatus::Applied,
            $consumer->consumeNext('audit-worker-1', ModerationOperationalAuditTest::now()),
        );
        $message = new ModerationDeliveryMessageV1(ModerationOperationalAuditTest::event());
        self::assertTrue($this->outbox->replay(
            $message->messageId,
            ModerationRoutingDestination::DeliveryObservation->value,
            ModerationOperationalAuditTest::now(),
        ));
        self::assertSame(
            ModerationOperationalAuditStatus::AlreadyApplied,
            $consumer->consumeNext('audit-worker-2', ModerationOperationalAuditTest::now()),
        );
        self::assertSame(
            1,
            (int) $auditConnection->query(
                'SELECT count(*) FROM administration_audit.public_append_records',
            )->fetchColumn(),
        );
    }

    #[Test]
    public function dependency_failure_retries_and_divergence_quarantines_without_a_partial_audit_record(): void
    {
        $unavailable = new class implements AdministrationAuditAppendV1
        {
            public function append(AdministrationAuditRecordV1 $record): AdministrationAuditAppendResultV1
            {
                return AdministrationAuditAppendResultV1::DependencyUnavailable;
            }
        };
        self::assertSame(
            ModerationOperationalAuditStatus::RetryScheduled,
            $this->consumer($unavailable)->consumeNext('audit-retry', ModerationOperationalAuditTest::now()),
        );
        self::assertSame(0, $this->auditCount());

        $delivery = $this->outbox->read(
            (new ModerationDeliveryMessageV1(ModerationOperationalAuditTest::event()))->messageId,
            ModerationRoutingDestination::DeliveryObservation->value,
        );
        self::assertNotNull($delivery);
        self::assertSame(1, $delivery->attempt);
        self::assertSame(
            ModerationOperationalAuditStatus::RetryScheduled,
            $this->consumer($unavailable)->consumeNext(
                'audit-retry',
                ModerationOperationalAuditTest::now()->modify('+2 seconds'),
            ),
        );
        self::assertSame(
            ModerationOperationalAuditStatus::Quarantined,
            $this->consumer($unavailable)->consumeNext(
                'audit-retry',
                ModerationOperationalAuditTest::now()->modify('+5 seconds'),
            ),
        );
        self::assertSame(0, $this->auditCount());
    }

    #[Test]
    public function a_divergent_audit_record_is_quarantined_without_overwrite(): void
    {
        $audit = new PostgreSqlAdministrationAuditAppendV1(
            new AdministrationAuditAppendConnection($this->connection),
            new AdministrationAuditAppendMapperV1,
        );
        $expected = (new ModerationOperationalAuditRecordFactory)
            ->map(ModerationOperationalAuditTest::event());
        self::assertNotNull($expected);
        $divergent = AdministrationAuditRecordV1::create(
            $expected->sourceOwner,
            $expected->operation,
            $expected->subjectId,
            $expected->actorId,
            AdministrationAuditOutcomeV1::Rejected,
            $expected->correlationId,
            $expected->causationId,
            $expected->occurredAt,
            $expected->policyVersion,
        );
        self::assertSame(AdministrationAuditAppendResultV1::Applied, $audit->append($divergent));

        self::assertSame(
            ModerationOperationalAuditStatus::Quarantined,
            $this->consumer($audit)->consumeNext(
                'audit-divergence',
                ModerationOperationalAuditTest::now(),
            ),
        );
        self::assertSame(1, $this->auditCount());
        self::assertSame(
            AdministrationAuditOutcomeV1::Rejected->value,
            $this->connection->query(
                'SELECT outcome FROM administration_audit.public_append_records',
            )->fetchColumn(),
        );
    }

    private function consumer(AdministrationAuditAppendV1 $audit): ModerationOperationalAuditConsumer
    {
        return new ModerationOperationalAuditConsumer(
            $this->outbox,
            $audit,
            new ModerationOperationalAuditRecordFactory,
        );
    }

    private function auditCount(): int
    {
        return (int) $this->connection->query(
            'SELECT count(*) FROM administration_audit.public_append_records',
        )->fetchColumn();
    }
}
