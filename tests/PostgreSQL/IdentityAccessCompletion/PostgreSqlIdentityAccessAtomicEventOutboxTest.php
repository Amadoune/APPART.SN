<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use App\Application\IdentityAccessEventIntegration\IdentityAccessAtomicEventOrchestrator;
use App\Application\IdentityAccessEventIntegration\IdentityAccessAtomicEventRequest;
use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxReader;
use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxWriteResult;
use App\Application\IdentityAccessEventRouting\DeterministicIdentityAccessEventRouter;
use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use App\Infrastructure\IdentityAccessEventOutbox\PostgreSql\PostgreSqlIdentityAccessOutbox;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\DeterministicIdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlIdentityAccessAtomicEventOutboxTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function business_state_intent_message_and_deliveries_commit_once(): void
    {
        $integration = $this->integration();
        $request = $this->request(IdentityAccessEventType::ProfileNameChanged);

        self::assertSame(IdentityAccessOrchestrationStatus::Applied, $integration->execute($request)->status);
        self::assertSame(IdentityAccessOrchestrationStatus::IdempotentReplay, $integration->execute($request)->status);
        self::assertSame(1, $this->tableCount('user_profiles'));
        self::assertSame(1, $this->tableCount('atomic_operation_intents'));
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertSame(1, $this->tableCount('event_outbox_deliveries'));
    }

    #[Test]
    public function incompatible_event_rolls_back_business_state_intent_and_outbox(): void
    {
        $result = $this->integration()->execute($this->request(IdentityAccessEventType::AccountClosed));

        self::assertSame(IdentityAccessOrchestrationStatus::RolledBack, $result->status);
        self::assertSame(0, $this->tableCount('user_profiles'));
        self::assertSame(0, $this->tableCount('atomic_operation_intents'));
        self::assertSame(0, $this->tableCount('event_outbox_messages'));
        self::assertSame(0, $this->tableCount('event_outbox_deliveries'));
    }

    #[Test]
    public function claim_retry_recovery_and_delivery_never_lose_or_duplicate_the_message(): void
    {
        self::assertSame(
            IdentityAccessOrchestrationStatus::Applied,
            $this->integration()->execute($this->request(IdentityAccessEventType::ProfileNameChanged))->status,
        );
        $reader = $this->outbox();
        $at = new DateTimeImmutable('2026-07-27T12:00:00Z');
        $claimed = $reader->claimNext('worker-1', $at);
        self::assertNotNull($claimed);
        self::assertSame(1, $claimed->attempt);
        self::assertNull($reader->claimNext('worker-2', $at));

        self::assertTrue($reader->scheduleRetry($claimed, $at->modify('+10 seconds'), 'temporary'));
        self::assertNull($reader->claimNext('worker-2', $at->modify('+9 seconds')));
        $recovered = $reader->claimNext('worker-2', $at->modify('+10 seconds'));
        self::assertNotNull($recovered);
        self::assertSame(2, $recovered->attempt);
        self::assertTrue($reader->markDelivered($recovered, $at->modify('+11 seconds')));
        self::assertNull($reader->claimNext('worker-3', $at->modify('+1 hour')));
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertSame(1, $this->tableCount('event_outbox_deliveries'));
    }

    #[Test]
    public function concurrent_identical_publications_converge_without_duplication(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'iam-outbox-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'outbox-concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start IAM Outbox worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException($error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertSame(1, $this->tableCount('event_outbox_deliveries'));
    }

    #[Test]
    public function same_event_identity_with_divergent_transport_is_rejected_without_sql_violation(): void
    {
        $outbox = $this->outbox();
        $first = new IdentityAccessDeliveryMessageV1($this->event(
            new DateTimeImmutable('2026-07-27T10:00:01Z'),
        ));
        $divergent = new IdentityAccessDeliveryMessageV1($this->event(
            new DateTimeImmutable('2026-07-27T10:00:02Z'),
        ));

        self::assertSame(
            IdentityAccessOutboxWriteResult::Applied,
            $outbox->append($first, [IdentityAccessRoutingDestination::PrivateAudit]),
        );
        self::assertSame($first->event->eventId, $divergent->event->eventId);
        self::assertNotSame($first->messageId, $divergent->messageId);
        self::assertSame(
            IdentityAccessOutboxWriteResult::DivergentMessage,
            $outbox->append($divergent, [IdentityAccessRoutingDestination::PrivateAudit]),
        );
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertSame(1, $this->tableCount('event_outbox_deliveries'));
    }

    private function integration(): IdentityAccessAtomicEventOrchestrator
    {
        $serializer = new IdentityAccessEventTransportSerializer;

        return new IdentityAccessAtomicEventOrchestrator(
            new DeterministicIdentityAccessOrchestrator(
                new PostgreSqlIdentityAccessAtomicTransaction($this->connection),
            ),
            new DeterministicIdentityAccessEventRouter($serializer),
            new PostgreSqlIdentityAccessOutbox($this->connection, $serializer),
        );
    }

    private function request(IdentityAccessEventType $type): IdentityAccessAtomicEventRequest
    {
        $command = new IdentityAccessAtomicCommand(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            str_repeat('a', 64),
            AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            IdentityAccessAtomicOperation::ProfileMutation,
            new DateTimeImmutable('2026-07-27T10:00:00Z'),
        );
        $event = new IdentityAccessEventV1(
            $type,
            $command->accountId,
            1,
            new DateTimeImmutable('2026-07-27T10:00:00Z'),
            new DateTimeImmutable('2026-07-27T10:00:01Z'),
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            $command->intentId,
        );

        return new IdentityAccessAtomicEventRequest(
            $command,
            [$event],
            function (): IdentityAccessAtomicWorkResult {
                $statement = $this->connection->prepare(
                    'INSERT INTO identity_access_completion.user_profiles
                     (account_id,display_name_ciphertext,normalization_version,version,enrolled_at,updated_at,last_intent_id,last_intent_checksum)
                     VALUES(:account_id,:name,:normalization,1,now(),now(),:intent_id,:checksum)',
                );
                $statement->execute([
                    'account_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                    'name' => 'ciphertext',
                    'normalization' => 'iam-profile-v1',
                    'intent_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                    'checksum' => str_repeat('a', 64),
                ]);

                return IdentityAccessAtomicWorkResult::Applied;
            },
        );
    }

    private function event(DateTimeImmutable $recordedAt): IdentityAccessEventV1
    {
        return new IdentityAccessEventV1(
            IdentityAccessEventType::ProfileNameChanged,
            AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            1,
            new DateTimeImmutable('2026-07-27T10:00:00Z'),
            $recordedAt,
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        );
    }

    private function outbox(): IdentityAccessOutboxReader
    {
        return new PostgreSqlIdentityAccessOutbox(
            $this->connection,
            new IdentityAccessEventTransportSerializer,
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query(
            "SELECT count(*) FROM identity_access_completion.{$table}",
        )->fetchColumn();
    }
}
