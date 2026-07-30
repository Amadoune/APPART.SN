<?php

namespace Tests\PostgreSQL\ProfessionalStatusEventIntegration;

use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalStatusAtomicEventIntegrationTest extends TestCase
{
    private PDO $pdo;

    private PostgreSqlProfessionalStatusWorkflowRepository $historical;

    private PostgreSqlProfessionalStatusContextualReplayInspector $inspector;

    private DeterministicProfessionalStatusOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        PostgreSqlTestEnvironment::reset($this->pdo);
        $mapper = new ProfessionalStatusWorkflowMapper;
        $context = new ProfessionalStatusContextMapper;
        $this->historical = new PostgreSqlProfessionalStatusWorkflowRepository($this->pdo, $mapper);
        $store = new PostgreSqlProfessionalStatusContextualTransitionRepository($this->pdo, $this->historical, $mapper, $context);
        $this->inspector = new PostgreSqlProfessionalStatusContextualReplayInspector($this->pdo, $mapper, $context);
        $this->orchestrator = new DeterministicProfessionalStatusOrchestrator($store, $this->inspector, new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);
    }

    /** @return iterable<string,array{ProfessionalStatusAction,string}> */
    public static function transitions(): iterable
    {
        yield 'suspend' => [ProfessionalStatusAction::Suspend, 'professional.status.suspended'];
        yield 'reactivate' => [ProfessionalStatusAction::Reactivate, 'professional.status.reactivated'];
    }

    #[DataProvider('transitions')]
    public function test_each_transition_and_event_commit_together(ProfessionalStatusAction $action, string $eventType): void
    {
        $this->historical->initialize($this->id());
        if ($action === ProfessionalStatusAction::Reactivate) {
            $this->integrator($this->writer())->transition($this->request(ProfessionalStatusAction::Suspend, 1));
            $expectedVersion = 2;
        } else {
            $expectedVersion = 1;
        }
        $result = $this->integrator($this->writer())->transition($this->request($action, $expectedVersion));
        self::assertSame(ProfessionalStatusOrchestrationStatus::Applied, $result->status);
        $row = $this->pdo->query('SELECT * FROM professionals.public_projection_outbox_messages ORDER BY aggregate_version DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($eventType, $row['event_type']);
        self::assertSame('Professionals', $row['source_module']);
        self::assertSame('ProfessionalStatus', $row['aggregate_type']);
    }

    public function test_denied_emits_nothing(): void
    {
        $this->historical->initialize($this->id());
        $result = $this->integrator($this->writer())->transition($this->request(ProfessionalStatusAction::Reactivate, 1));
        self::assertSame(ProfessionalStatusOrchestrationStatus::Denied, $result->status);
        self::assertSame(0, $this->outboxCount());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM professionals.professional_status_transition_contexts')->fetchColumn());
    }

    public function test_outbox_rejection_rolls_back_transition_and_context(): void
    {
        $this->historical->initialize($this->id());
        $result = $this->integrator(new RejectingProfessionalStatusOutboxWriter)->transition($this->request(ProfessionalStatusAction::Suspend, 1));
        self::assertSame(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM professionals.professional_status_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM professionals.professional_status_transition_contexts')->fetchColumn());
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_is_idempotent_and_divergence_emits_nothing(): void
    {
        $this->historical->initialize($this->id());
        $integrator = $this->integrator($this->writer());
        self::assertSame(ProfessionalStatusOrchestrationStatus::Applied, $integrator->transition($this->request(ProfessionalStatusAction::Suspend, 1))->status);
        self::assertSame(ProfessionalStatusOrchestrationStatus::AlreadyApplied, $integrator->transition($this->request(ProfessionalStatusAction::Suspend, 1))->status);
        self::assertSame(ProfessionalStatusOrchestrationStatus::ContextDivergence, $integrator->transition($this->request(ProfessionalStatusAction::Suspend, 1, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'))->status);
        self::assertSame(1, $this->outboxCount());
    }

    public function test_concurrent_identical_requests_commit_one_transition_context_and_event(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000402');
        $this->historical->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'professional-status-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Professional Status atomic worker.');
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
                throw new RuntimeException('Professional Status atomic worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='a4500000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='a4500000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, $this->outboxCount());
    }

    private function integrator(PublicProjectionOutboxWriter $writer): ProfessionalStatusAtomicEventOrchestrator
    {
        return new ProfessionalStatusAtomicEventOrchestrator($this->orchestrator, $this->inspector, new PostgreSqlAggregateOutboxTransaction($this->pdo), new ProfessionalStatusEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $writer, PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
    }

    private function writer(): PostgreSqlPublicProjectionOutboxWriter
    {
        return new PostgreSqlPublicProjectionOutboxWriter($this->pdo, new PostgreSqlPublicProjectionOutboxMapper);
    }

    private function request(ProfessionalStatusAction $action, int $version, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): ProfessionalStatusAtomicEventRequest
    {
        $occurred = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:00Z'));

        return new ProfessionalStatusAtomicEventRequest($this->id(), $action, new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString($actor), $occurred, new ProfessionalStatusExpectedVersion($version)), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:01Z')));
    }

    private function id(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000401');
    }

    private function outboxCount(): int
    {
        return (int) $this->pdo->query('SELECT count(*) FROM professionals.public_projection_outbox_messages')->fetchColumn();
    }
}

final class RejectingProfessionalStatusOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }
}
