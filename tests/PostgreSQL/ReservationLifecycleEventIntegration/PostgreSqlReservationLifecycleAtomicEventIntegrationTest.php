<?php

namespace Tests\PostgreSQL\ReservationLifecycleEventIntegration;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventRequest;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\DeterministicReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReservationLifecycleAtomicEventIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    /** @return iterable<string, array{ReservationLifecycleState,ReservationLifecycleAction,string}> */
    public static function transitions(): iterable
    {
        yield 'submitted' => [ReservationLifecycleState::Draft, ReservationLifecycleAction::Submit, 'reservation.lifecycle.submitted'];
        yield 'cancelled draft' => [ReservationLifecycleState::Draft, ReservationLifecycleAction::Cancel, 'reservation.lifecycle.cancelled_from_draft'];
        yield 'confirmed' => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Confirm, 'reservation.lifecycle.confirmed'];
        yield 'rejected' => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Reject, 'reservation.lifecycle.rejected'];
        yield 'cancelled requested' => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Cancel, 'reservation.lifecycle.cancelled_from_requested'];
        yield 'expired requested' => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Expire, 'reservation.lifecycle.expired_from_requested'];
        yield 'started' => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Start, 'reservation.lifecycle.started'];
        yield 'cancelled confirmed' => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Cancel, 'reservation.lifecycle.cancelled_from_confirmed'];
        yield 'expired confirmed' => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Expire, 'reservation.lifecycle.expired_from_confirmed'];
        yield 'completed' => [ReservationLifecycleState::InProgress, ReservationLifecycleAction::Complete, 'reservation.lifecycle.completed'];
        yield 'cancelled in progress' => [ReservationLifecycleState::InProgress, ReservationLifecycleAction::Cancel, 'reservation.lifecycle.cancelled_in_progress'];
    }

    #[DataProvider('transitions')]
    public function test_each_certified_transition_commits_with_its_exact_event(ReservationLifecycleState $initial, ReservationLifecycleAction $action, string $eventType): void
    {
        $store = $this->store();
        $store->initialize($this->id(), $initial);

        $result = $this->orchestrator($store, $this->writer())->transition($this->request($action));

        self::assertSame(ReservationLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(2, $store->read($this->id())->snapshot?->version);
        $row = $this->outboxRow();
        self::assertSame($eventType, $row['event_type']);
        self::assertSame('ReservationLifecycle', $row['source_module']);
        self::assertSame('ReservationLifecycle', $row['aggregate_type']);
        self::assertSame(2, $row['aggregate_version']);
        $fields = json_decode($row['payload'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(hash('sha256', $fields['canonicalEvent']), $row['payload_checksum']);
        self::assertSame($fields['canonicalEvent'], json_encode(json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function test_denied_or_stale_transition_writes_no_event(): void
    {
        $store = $this->store();
        $store->initialize($this->id(), ReservationLifecycleState::Draft);
        $orchestrator = $this->orchestrator($store, $this->writer());

        self::assertSame(ReservationLifecycleOrchestrationStatus::Denied, $orchestrator->transition($this->request(ReservationLifecycleAction::Complete))->status);
        self::assertSame(ReservationLifecycleOrchestrationStatus::VersionConflict, $orchestrator->transition(new ReservationLifecycleAtomicEventRequest($this->id(), ReservationLifecycleAction::Submit, 2, $this->occurredAt(), $this->recordedAt()))->status);
        self::assertSame(1, $store->read($this->id())->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_outbox_failure_rolls_back_the_journal_transition(): void
    {
        $store = $this->store();
        $store->initialize($this->id(), ReservationLifecycleState::Draft);

        $result = $this->orchestrator($store, new RejectingReservationOutboxWriter)->transition($this->request(ReservationLifecycleAction::Submit));

        self::assertSame(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(ReservationLifecycleState::Draft, $store->read($this->id())->snapshot?->state);
        self::assertSame(1, $store->read($this->id())->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_persistence_failure_rolls_back_any_partial_journal_write(): void
    {
        $store = $this->store();
        $integrator = $this->integrator(new PartiallyFailingReservationOrchestrator($store), $this->writer());

        $result = $integrator->transition($this->request(ReservationLifecycleAction::Submit));

        self::assertSame(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_is_idempotent(): void
    {
        $store = $this->store();
        $store->initialize($this->id(), ReservationLifecycleState::Draft);
        $orchestrator = $this->orchestrator($store, $this->writer());

        self::assertSame(ReservationLifecycleOrchestrationStatus::Applied, $orchestrator->transition($this->request(ReservationLifecycleAction::Submit))->status);
        self::assertSame(ReservationLifecycleOrchestrationStatus::VersionConflict, $orchestrator->transition($this->request(ReservationLifecycleAction::Submit))->status);
        self::assertSame(2, $store->read($this->id())->snapshot?->version);
        self::assertSame(1, $this->outboxCount());
    }

    public function test_concurrent_identical_requests_commit_one_transition_and_one_event(): void
    {
        $store = $this->store();
        $store->initialize($this->id(), ReservationLifecycleState::Draft);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-reservation-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start reservation atomic worker.');
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
                throw new RuntimeException('Reservation atomic worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(1, count(array_filter($results, static fn (string $result): bool => $result === 'applied')));
        $secondResult = array_values(array_filter($results, static fn (string $result): bool => $result !== 'applied'));
        self::assertCount(1, $secondResult);
        self::assertTrue(in_array($secondResult[0], ['already_applied', 'version_conflict'], true));
        self::assertSame(2, $store->read($this->id())->snapshot?->version);
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_transitions')->fetchColumn());
        self::assertSame(1, $this->outboxCount());
    }

    private function orchestrator(PostgreSqlReservationLifecycleWorkflowRepository $store, PublicProjectionOutboxWriter $writer): ReservationLifecycleAtomicEventOrchestrator
    {
        return $this->integrator(new DeterministicReservationLifecycleEventOrchestrator(new ReservationLifecycleWorkflow, $store), $writer);
    }

    private function integrator(ReservationLifecycleEventOrchestrator $orchestrator, PublicProjectionOutboxWriter $writer): ReservationLifecycleAtomicEventOrchestrator
    {
        return new ReservationLifecycleAtomicEventOrchestrator($orchestrator, new PostgreSqlAggregateOutboxTransaction($this->connection), new ReservationLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $writer, PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
    }

    private function store(): PostgreSqlReservationLifecycleWorkflowRepository
    {
        return new PostgreSqlReservationLifecycleWorkflowRepository($this->connection, new ReservationLifecycleWorkflowMapper);
    }

    private function writer(): PostgreSqlPublicProjectionOutboxWriter
    {
        return new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper);
    }

    private function request(ReservationLifecycleAction $action): ReservationLifecycleAtomicEventRequest
    {
        return new ReservationLifecycleAtomicEventRequest($this->id(), $action, 1, $this->occurredAt(), $this->recordedAt());
    }

    private function id(): ReservationId
    {
        return ReservationId::fromString('22222222-2222-4222-8222-222222222222');
    }

    private function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-21T10:00:00.000000Z');
    }

    private function recordedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-21T10:00:01.000000Z');
    }

    /** @return array<string, mixed> */
    private function outboxRow(): array
    {
        $row = $this->connection->query('SELECT * FROM reservation_lifecycle.public_projection_outbox_messages')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);

        return $row;
    }

    private function outboxCount(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.public_projection_outbox_messages')->fetchColumn();
    }
}

final readonly class PartiallyFailingReservationOrchestrator implements ReservationLifecycleEventOrchestrator
{
    public function __construct(private PostgreSqlReservationLifecycleWorkflowRepository $store) {}

    public function transition(ReservationLifecycleOrchestrationRequest $request): ReservationLifecycleOrchestrationResult
    {
        $this->store->initialize($request->reservationId, ReservationLifecycleState::Draft);

        return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
    }
}

final readonly class RejectingReservationOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }
}
