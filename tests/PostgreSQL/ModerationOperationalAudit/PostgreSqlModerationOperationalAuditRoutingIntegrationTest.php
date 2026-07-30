<?php

namespace Tests\PostgreSQL\ModerationOperationalAudit;

use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditConsumer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditStatus;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditDeliveryReader;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditOutboxAppender;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\QueueItemClaimedEventV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationOperationalAuditRoutingIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function seven_routes_append_exactly_once_and_replay_converges(): void
    {
        $appender = new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection);
        $historical = new PostgreSqlModerationOutbox(
            $this->connection,
            new ModerationEventTransportSerializer,
            new DeterministicModerationEventRouter,
        );
        foreach ($this->messages() as $index => $message) {
            $index < 4
                ? $appender->append($message)
                : $historical->append(new ModerationDeliveryMessageV1($message->event));
        }
        $consumer = $this->consumer(new PostgreSqlAdministrationAuditAppendV1(
            new AdministrationAuditAppendConnection(PostgreSqlTestEnvironment::connection()),
            new AdministrationAuditAppendMapperV1,
        ));
        for ($index = 0; $index < 7; $index++) {
            self::assertSame(
                ModerationOperationalAuditStatus::Applied,
                $consumer->consumeNext('audit-worker', $this->now()),
            );
        }
        self::assertSame(7, $this->auditCount());

        $message = $this->messages()[0];
        $replay = $this->connection->prepare(
            "UPDATE moderation_reports.outbox_deliveries
             SET status='Pending',attempts=0,claim_owner=NULL,claimed_until=NULL
             WHERE message_id=:message_id AND destination='moderation.delivery-observation'",
        );
        $replay->execute(['message_id' => $message->messageId]);
        self::assertSame(
            ModerationOperationalAuditStatus::AlreadyApplied,
            $consumer->consumeNext('audit-replay', $this->now()),
        );
        self::assertSame(7, $this->auditCount());
    }

    #[Test]
    public function dependency_failure_never_marks_delivered_and_event_corruption_is_quarantined(): void
    {
        $message = $this->messages()[0];
        (new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection))->append($message);
        $unavailable = new class implements AdministrationAuditAppendV1
        {
            public function append(AdministrationAuditRecordV1 $record): AdministrationAuditAppendResultV1
            {
                return AdministrationAuditAppendResultV1::DependencyUnavailable;
            }
        };
        self::assertSame(
            ModerationOperationalAuditStatus::RetryScheduled,
            $this->consumer($unavailable)->consumeNext('audit-retry', $this->now()),
        );
        self::assertSame(
            'Retry',
            $this->connection->query(
                "SELECT status FROM moderation_reports.outbox_deliveries
                 WHERE destination='moderation.delivery-observation'",
            )->fetchColumn(),
        );
        self::assertSame(0, $this->auditCount());

        $this->connection->exec(
            "UPDATE moderation_reports.outbox_messages
             SET canonical_transport=jsonb_set(canonical_transport,'{payload,checksum}','\""
            .str_repeat('0', 64)."\"'::jsonb)",
        );
        $this->connection->exec(
            "UPDATE moderation_reports.outbox_deliveries
             SET status='Pending',available_at=now(),claim_owner=NULL,claimed_until=NULL",
        );
        self::assertSame(
            ModerationOperationalAuditStatus::Quarantined,
            $this->consumer($unavailable)->consumeNext('audit-corrupt', $this->now()),
        );
    }

    private function consumer(AdministrationAuditAppendV1 $audit): ModerationOperationalAuditConsumer
    {
        return new ModerationOperationalAuditConsumer(
            new PostgreSqlModerationOperationalAuditDeliveryReader($this->connection),
            $audit,
            new ModerationOperationalAuditRecordFactory,
        );
    }

    /** @return list<ModerationOperationalAuditOutboxMessageV1> */
    private function messages(): array
    {
        return [
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::ReportSubmitted,
                $this->id(1), 1, ['reportId' => $this->id(2)], 'v1',
                $this->now(), $this->now(), $this->id(3), $this->id(4),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::ReportValidated,
                $this->id(1), 2, ['reportId' => $this->id(2)], 'v1',
                $this->now(), $this->now(), $this->id(5), $this->id(6),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new FindingRecordedEventV1(
                $this->id(1), $this->id(7), 3, 'v1',
                $this->now(), $this->now(), $this->id(8), $this->id(9),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new QueueItemClaimedEventV1(
                $this->id(1), $this->id(10), 4, 'v1',
                $this->now(), $this->now(), $this->id(11), $this->id(12),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::DecisionIssued,
                $this->id(1), 5, ['decisionId' => $this->id(13)], 'v1',
                $this->now(), $this->now(), $this->id(14), $this->id(15),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::CaseClosed,
                $this->id(1), 6, ['currentDecisionId' => $this->id(13)], 'v1',
                $this->now(), $this->now(), $this->id(16), $this->id(17),
            )),
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::TargetActionCompleted,
                $this->id(1), 7, [
                    'decisionId' => $this->id(13),
                    'result' => 'applied',
                    'targetActionId' => $this->id(18),
                ], 'v1',
                $this->now(), $this->now(), $this->id(19), $this->id(20),
            )),
        ];
    }

    private function auditCount(): int
    {
        return (int) $this->connection->query(
            'SELECT count(*) FROM administration_audit.public_append_records',
        )->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
