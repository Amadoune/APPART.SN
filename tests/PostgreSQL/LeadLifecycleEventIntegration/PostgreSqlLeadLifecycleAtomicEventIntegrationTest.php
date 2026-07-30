<?php

namespace Tests\PostgreSQL\LeadLifecycleEventIntegration;

use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventRequest;
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
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadLifecycleAtomicEventIntegrationTest extends TestCase
{
    private PDO $pdo;

    private PostgreSqlLeadLifecycleWorkflowRepository $historical;

    private PostgreSqlLeadLifecycleContextualReplayInspector $inspector;

    private DeterministicLeadLifecycleOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        PostgreSqlTestEnvironment::reset($this->pdo);
        $mapper = new LeadLifecycleWorkflowMapper;
        $context = new LeadLifecycleContextMapper;
        $this->historical = new PostgreSqlLeadLifecycleWorkflowRepository($this->pdo, $mapper);
        $store = new PostgreSqlLeadLifecycleContextualTransitionRepository($this->pdo, $this->historical, $mapper, $context);
        $this->inspector = new PostgreSqlLeadLifecycleContextualReplayInspector($this->pdo, $mapper, $context);
        $this->orchestrator = new DeterministicLeadLifecycleOrchestrator($store, $this->inspector, new LeadLifecycleWorkflow);
    }

    /** @return iterable<string, array{LeadLifecycleState,LeadLifecycleAction,string}> */
    public static function transitions(): iterable
    {
        yield 'delivered' => [LeadLifecycleState::Created, LeadLifecycleAction::Deliver, 'lead.lifecycle.delivered'];
        yield 'rejected' => [LeadLifecycleState::Created, LeadLifecycleAction::Reject, 'lead.lifecycle.rejected'];
        yield 'closed after delivery' => [LeadLifecycleState::Delivered, LeadLifecycleAction::Close, 'lead.lifecycle.closed'];
        yield 'closed after rejection' => [LeadLifecycleState::Rejected, LeadLifecycleAction::Close, 'lead.lifecycle.closed'];
    }

    #[DataProvider('transitions')]
    public function test_each_transition_and_event_commit_together(LeadLifecycleState $initial, LeadLifecycleAction $action, string $eventType): void
    {
        $this->historical->initialize($this->id(), $initial);
        $result = $this->integrator($this->writer())->transition($this->request($action));

        self::assertSame(LeadLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(2, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
        $row = $this->outboxRow();
        self::assertSame($eventType, $row['event_type']);
        self::assertSame('ContactsLeads', $row['source_module']);
        self::assertSame('LeadLifecycle', $row['aggregate_type']);
        $fields = json_decode($row['payload'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(hash('sha256', $fields['canonicalEvent']), $row['payload_checksum']);
    }

    public function test_denied_writes_neither_context_nor_event(): void
    {
        $this->historical->initialize($this->id(), LeadLifecycleState::Created);
        $result = $this->integrator($this->writer())->transition($this->request(LeadLifecycleAction::Close));

        self::assertSame(LeadLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
        self::assertSame(0, $this->outboxCount());
    }

    public function test_outbox_rejection_rolls_back_transition_and_context(): void
    {
        $this->historical->initialize($this->id(), LeadLifecycleState::Created);
        $result = $this->integrator(new RejectingLeadOutboxWriter)->transition($this->request(LeadLifecycleAction::Deliver));

        self::assertSame(LeadLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_is_idempotent_and_context_divergence_emits_nothing(): void
    {
        $this->historical->initialize($this->id(), LeadLifecycleState::Created);
        $integrator = $this->integrator($this->writer());

        self::assertSame(LeadLifecycleOrchestrationStatus::Applied, $integrator->transition($this->request(LeadLifecycleAction::Deliver))->status);
        self::assertSame(LeadLifecycleOrchestrationStatus::AlreadyApplied, $integrator->transition($this->request(LeadLifecycleAction::Deliver))->status);
        self::assertSame(LeadLifecycleOrchestrationStatus::ContextDivergence, $integrator->transition($this->request(LeadLifecycleAction::Deliver, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'))->status);
        self::assertSame(2, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
        self::assertSame(1, $this->outboxCount());
    }

    public function test_concurrent_identical_requests_commit_one_transition_context_and_event(): void
    {
        $id = LeadId::fromString('a4400000-0000-4000-8000-000000000402');
        $this->historical->initialize($id, LeadLifecycleState::Created);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lead-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Lead atomic worker.');
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
                throw new RuntimeException('Lead atomic worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions WHERE lead_id='a4400000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts WHERE lead_id='a4400000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, $this->outboxCount());
    }

    private function integrator(PublicProjectionOutboxWriter $writer): LeadLifecycleAtomicEventOrchestrator
    {
        return new LeadLifecycleAtomicEventOrchestrator($this->orchestrator, $this->inspector, new PostgreSqlAggregateOutboxTransaction($this->pdo), new LeadLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $writer, PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
    }

    private function writer(): PostgreSqlPublicProjectionOutboxWriter
    {
        return new PostgreSqlPublicProjectionOutboxWriter($this->pdo, new PostgreSqlPublicProjectionOutboxMapper);
    }

    private function request(LeadLifecycleAction $action, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): LeadLifecycleAtomicEventRequest
    {
        return new LeadLifecycleAtomicEventRequest($this->id(), $action, 1, LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:00Z')), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:01Z')));
    }

    private function id(): LeadId
    {
        return LeadId::fromString('a4400000-0000-4000-8000-000000000401');
    }

    /** @return array<string, mixed> */
    private function outboxRow(): array
    {
        $row = $this->pdo->query('SELECT * FROM contacts_leads.public_projection_outbox_messages')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);

        return $row;
    }

    private function outboxCount(): int
    {
        return (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.public_projection_outbox_messages')->fetchColumn();
    }
}

final class RejectingLeadOutboxWriter implements PublicProjectionOutboxWriter
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
