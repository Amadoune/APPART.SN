<?php

namespace Tests\PostgreSQL\AdministrativeActionLifecycleEventIntegration;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventRequest;
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
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\DeterministicAdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransitionContextMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualTransitionRepository;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrativeActionLifecycleAtomicEventIntegrationTest extends TestCase
{
    private PDO $pdo;

    private PostgreSqlAdministrativeActionLifecycleRepository $lifecycle;

    private PostgreSqlAdministrativeActionContextualReplayInspector $inspector;

    private DeterministicAdministrativeActionLifecycleOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        PostgreSqlTestEnvironment::reset($this->pdo);
        $workflowMapper = new AdministrativeActionLifecycleWorkflowMapper;
        $this->lifecycle = new PostgreSqlAdministrativeActionLifecycleRepository(
            $this->pdo,
            $workflowMapper,
            new AdministrativeActionEnrollmentCanonicalizer,
        );
        $store = new PostgreSqlAdministrativeActionContextualTransitionRepository(
            $this->pdo,
            $this->lifecycle,
            new AdministrativeActionTransitionContextMapper,
        );
        $this->inspector = new PostgreSqlAdministrativeActionContextualReplayInspector(
            $this->pdo,
            $workflowMapper,
        );
        $this->orchestrator = new DeterministicAdministrativeActionLifecycleOrchestrator(
            $store,
            $this->inspector,
            new AdministrativeActionReplayPolicy,
            new AdministrativeActionLifecycleWorkflow,
        );
    }

    public function test_transition_context_mirror_event_and_outbox_commit_together(): void
    {
        $this->seedEnrolled($this->id());

        $result = $this->integrator($this->writer())->transition($this->request($this->id()));

        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(2, $this->lifecycleCount($this->id()));
        self::assertSame(1, $this->contextCount($this->id()));
        self::assertSame('recorded', $this->historicalStatus($this->id()));
        $row = $this->pdo->query('SELECT * FROM administration_audit.public_projection_outbox_messages LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame('administrative.action.lifecycle.recorded', $row['event_type']);
        self::assertSame('AdministrationAudit', $row['source_module']);
        self::assertSame('AdministrativeActionLifecycle', $row['aggregate_type']);
    }

    /** @return iterable<string, array{AdministrativeActionLifecycleAction, string}> */
    public static function approvalDecisions(): iterable
    {
        yield 'approve' => [
            AdministrativeActionLifecycleAction::Approve,
            'administrative.action.lifecycle.approved',
        ];
        yield 'reject' => [
            AdministrativeActionLifecycleAction::Reject,
            'administrative.action.lifecycle.rejected',
        ];
    }

    #[DataProvider('approvalDecisions')]
    public function test_approval_requested_and_terminal_decision_each_produce_one_event(
        AdministrativeActionLifecycleAction $terminalAction,
        string $terminalEvent,
    ): void {
        $this->seedEnrolled($this->id(), true);
        $integrator = $this->integrator($this->writer());

        $recorded = $integrator->transition($this->approvalRequest($this->id()));
        $decided = $integrator->transition($this->decisionRequest($this->id(), $terminalAction));

        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::Applied, $recorded->status);
        self::assertSame(
            AdministrativeActionLifecycleOrchestrationStatus::Applied,
            $decided->status,
            json_encode([
                'lifecycle' => $this->lifecycleCount($this->id()),
                'contexts' => $this->contextCount($this->id()),
                'historical' => $this->historicalStatus($this->id()),
                'outbox' => $this->outboxCount(),
            ], JSON_THROW_ON_ERROR),
        );
        $events = $this->pdo->query('SELECT event_type FROM administration_audit.public_projection_outbox_messages ORDER BY aggregate_version')->fetchAll(PDO::FETCH_COLUMN);
        self::assertSame([
            'administrative.action.lifecycle.approval_requested',
            $terminalEvent,
        ], $events);
        self::assertSame(3, $this->lifecycleCount($this->id()));
        self::assertSame(2, $this->contextCount($this->id()));
        self::assertSame($terminalAction === AdministrativeActionLifecycleAction::Approve ? 'approved' : 'rejected', $this->historicalStatus($this->id()));
    }

    public function test_outbox_rejection_rolls_back_journal_context_and_historical_mirror(): void
    {
        $this->seedEnrolled($this->id());

        $result = $this->integrator(new RejectingAdministrativeActionLifecycleOutboxWriter)
            ->transition($this->request($this->id()));

        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(1, $this->lifecycleCount($this->id()));
        self::assertSame(0, $this->contextCount($this->id()));
        self::assertSame('draft', $this->historicalStatus($this->id()));
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_does_not_duplicate_any_persisted_fact(): void
    {
        $this->seedEnrolled($this->id());
        $integrator = $this->integrator($this->writer());

        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::Applied, $integrator->transition($this->request($this->id()))->status);
        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied, $integrator->transition($this->request($this->id()))->status);
        self::assertSame(2, $this->lifecycleCount($this->id()));
        self::assertSame(1, $this->contextCount($this->id()));
        self::assertSame(1, $this->outboxCount());
    }

    public function test_concurrent_identical_requests_commit_exactly_one_transition_context_and_event(): void
    {
        $id = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000471');
        $this->seedEnrolled($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'admin-action-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Administrative Action Lifecycle atomic worker.');
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
                throw new RuntimeException('Administrative Action Lifecycle atomic worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, $this->lifecycleCount($id));
        self::assertSame(1, $this->contextCount($id));
        self::assertSame(1, $this->outboxCount());
    }

    private function integrator(
        PublicProjectionOutboxWriter $writer,
    ): AdministrativeActionLifecycleAtomicEventOrchestrator {
        return new AdministrativeActionLifecycleAtomicEventOrchestrator(
            $this->orchestrator,
            $this->inspector,
            new PostgreSqlAggregateOutboxTransaction($this->pdo),
            new AdministrativeActionLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
        );
    }

    private function writer(): PostgreSqlPublicProjectionOutboxWriter
    {
        return new PostgreSqlPublicProjectionOutboxWriter(
            $this->pdo,
            new PostgreSqlPublicProjectionOutboxMapper,
        );
    }

    private function request(
        AdministrativeActionId $id,
    ): AdministrativeActionLifecycleAtomicEventRequest {
        $actor = ActorId::fromString('author-001');
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-25T10:00:00Z'),
        );
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::directRecording($actor, $actor),
        );

        return new AdministrativeActionLifecycleAtomicEventRequest(
            $id,
            AdministrativeActionLifecycleAction::Record,
            AdministrativeActionTransitionExecutionContext::record(
                new AdministrativeActionExpectedVersion(1),
                $actor,
                $occurredAt,
                AuditReason::fromString('Administrative atomic integration reason.'),
                $decision,
            ),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
                new DateTimeImmutable('2026-07-25T10:00:01Z'),
            ),
        );
    }

    private function approvalRequest(
        AdministrativeActionId $id,
    ): AdministrativeActionLifecycleAtomicEventRequest {
        $author = ActorId::fromString('author-001');
        $decisionActor = ActorId::fromString('decision-actor-001');
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decisionActor),
        );
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-25T10:00:00Z'),
        );

        return new AdministrativeActionLifecycleAtomicEventRequest(
            $id,
            AdministrativeActionLifecycleAction::Record,
            AdministrativeActionTransitionExecutionContext::record(
                new AdministrativeActionExpectedVersion(1),
                $author,
                $occurredAt,
                AuditReason::fromString('Administrative atomic integration reason.'),
                $decision,
            ),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
                new DateTimeImmutable('2026-07-25T10:00:01Z'),
            ),
        );
    }

    private function decisionRequest(
        AdministrativeActionId $id,
        AdministrativeActionLifecycleAction $action,
    ): AdministrativeActionLifecycleAtomicEventRequest {
        $author = ActorId::fromString('author-001');
        $decisionActor = ActorId::fromString('decision-actor-001');
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decisionActor),
        );
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-25T10:01:00Z'),
        );
        $context = $action === AdministrativeActionLifecycleAction::Approve
            ? AdministrativeActionTransitionExecutionContext::approve(
                new AdministrativeActionExpectedVersion(2),
                $decisionActor,
                $occurredAt,
                ApprovalId::fromString('a4700000-0000-4000-8000-000000000001'),
                DecisionId::fromString('a4700000-0000-4000-8000-000000000002'),
                AuditReason::fromString('Administrative approval decision reason.'),
                $decision,
            )
            : AdministrativeActionTransitionExecutionContext::reject(
                new AdministrativeActionExpectedVersion(2),
                $decisionActor,
                $occurredAt,
                DecisionId::fromString('a4700000-0000-4000-8000-000000000003'),
                AuditReason::fromString('Administrative rejection decision reason.'),
                $decision,
            );

        return new AdministrativeActionLifecycleAtomicEventRequest(
            $id,
            $action,
            $context,
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
                new DateTimeImmutable('2026-07-25T10:01:01Z'),
            ),
        );
    }

    private function seedEnrolled(AdministrativeActionId $id, bool $requiresFourEyes = false): void
    {
        $statement = $this->pdo->prepare('INSERT INTO administration_audit.administrative_actions (id,author_id,target_id,action_type,requires_four_eyes,last_changed_at,status,reason,version) VALUES(CAST(:id AS uuid),:author,:target,:type,:requires_four_eyes,:changed,:status,:reason,1)');
        $statement->execute([
            'id' => $id->value,
            'author' => 'author-001',
            'target' => 'identity:atomic-target',
            'type' => 'atomic_integration',
            'requires_four_eyes' => $requiresFourEyes ? 'true' : 'false',
            'changed' => '2026-07-25T09:00:00Z',
            'status' => 'draft',
            'reason' => 'Administrative atomic integration reason.',
        ]);
        $this->lifecycle->enroll(
            (new AdministrativeActionEnrollmentCanonicalizer)->checkpoint(
                $id,
                1,
                AdministrativeActionLifecycleState::Draft,
            ),
        );
    }

    private function id(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000470');
    }

    private function lifecycleCount(AdministrativeActionId $id): int
    {
        return (int) $this->pdo->query("SELECT count(*) FROM administration_audit.administrative_action_lifecycle_transitions WHERE action_id='".$id->value."'")->fetchColumn();
    }

    private function contextCount(AdministrativeActionId $id): int
    {
        return (int) $this->pdo->query("SELECT count(*) FROM administration_audit.administrative_action_lifecycle_transition_contexts WHERE action_id='".$id->value."'")->fetchColumn();
    }

    private function historicalStatus(AdministrativeActionId $id): string
    {
        return (string) $this->pdo->query("SELECT status FROM administration_audit.administrative_actions WHERE id='".$id->value."'")->fetchColumn();
    }

    private function outboxCount(): int
    {
        return (int) $this->pdo->query('SELECT count(*) FROM administration_audit.public_projection_outbox_messages')->fetchColumn();
    }
}

final class RejectingAdministrativeActionLifecycleOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function markDelivered(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
        PublicProjectionOutboxClaimOwnerId $ownerId,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function scheduleRetry(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
        PublicProjectionOutboxClaimOwnerId $ownerId,
        PublicProjectionOutboxRetryDecision $decision,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function quarantine(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
        ?PublicProjectionOutboxClaimOwnerId $ownerId,
        PublicProjectionOutboxQuarantineDecision $decision,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }

    public function releaseClaim(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
        PublicProjectionOutboxClaimOwnerId $ownerId,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::InvalidTransition;
    }
}
