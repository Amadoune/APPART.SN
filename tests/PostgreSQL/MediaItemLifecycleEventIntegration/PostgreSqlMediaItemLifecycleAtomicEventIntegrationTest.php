<?php

namespace Tests\PostgreSQL\MediaItemLifecycleEventIntegration;

use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventRequest;
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
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaItemLifecycleAtomicEventIntegrationTest extends TestCase
{
    private PDO $pdo;

    private PostgreSqlMediaItemLifecycleWorkflowRepository $historical;

    private PostgreSqlMediaItemLifecycleContextualReplayInspector $inspector;

    private DeterministicMediaItemLifecycleOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        PostgreSqlTestEnvironment::reset($this->pdo);
        $mapper = new MediaItemLifecycleWorkflowMapper;
        $context = new MediaItemLifecycleContextMapper;
        $this->historical = new PostgreSqlMediaItemLifecycleWorkflowRepository($this->pdo, $mapper);
        $store = new PostgreSqlMediaItemLifecycleContextualTransitionRepository($this->pdo, $this->historical, $mapper, $context);
        $this->inspector = new PostgreSqlMediaItemLifecycleContextualReplayInspector($this->pdo, $mapper);
        $this->orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, $this->inspector, new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);
    }

    /** @return iterable<string,array{MediaItemLifecycleAction,string}> */
    public static function transitions(): iterable
    {
        yield 'remove' => [MediaItemLifecycleAction::Remove, 'media.item.lifecycle.removed'];
        yield 'archive' => [MediaItemLifecycleAction::Archive, 'media.item.lifecycle.archived'];
    }

    #[DataProvider('transitions')]
    public function test_each_transition_and_event_commit_together(MediaItemLifecycleAction $action, string $eventType): void
    {
        $this->historical->initialize($this->id());

        $result = $this->integrator($this->writer())->transition($this->request($action));

        self::assertSame(MediaItemLifecycleOrchestrationStatus::Applied, $result->status);
        $row = $this->pdo->query('SELECT * FROM media.public_projection_outbox_messages LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($eventType, $row['event_type']);
        self::assertSame('Media', $row['source_module']);
        self::assertSame('MediaItemLifecycle', $row['aggregate_type']);
    }

    public function test_denied_emits_nothing(): void
    {
        $this->historical->initialize($this->id());

        $result = $this->integrator($this->writer())->transition($this->request(MediaItemLifecycleAction::Unknown));

        self::assertSame(MediaItemLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertSame(0, $this->outboxCount());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM media.media_item_lifecycle_transition_contexts')->fetchColumn());
    }

    public function test_outbox_rejection_rolls_back_transition_and_context(): void
    {
        $this->historical->initialize($this->id());

        $result = $this->integrator(new RejectingMediaItemLifecycleOutboxWriter)->transition($this->request(MediaItemLifecycleAction::Remove));

        self::assertSame(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM media.media_item_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM media.media_item_lifecycle_transition_contexts')->fetchColumn());
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_is_idempotent_and_divergence_emits_nothing(): void
    {
        $this->historical->initialize($this->id());
        $integrator = $this->integrator($this->writer());

        self::assertSame(MediaItemLifecycleOrchestrationStatus::Applied, $integrator->transition($this->request(MediaItemLifecycleAction::Remove))->status);
        self::assertSame(MediaItemLifecycleOrchestrationStatus::AlreadyApplied, $integrator->transition($this->request(MediaItemLifecycleAction::Remove))->status);
        self::assertSame(MediaItemLifecycleOrchestrationStatus::ContextDivergence, $integrator->transition($this->request(MediaItemLifecycleAction::Remove, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'))->status);
        self::assertSame(1, $this->outboxCount());
    }

    public function test_concurrent_identical_requests_commit_one_transition_context_and_event(): void
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000402');
        $this->historical->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'media-item-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Media Item Lifecycle atomic worker.');
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
                throw new RuntimeException('Media Item Lifecycle atomic worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM media.media_item_lifecycle_transitions WHERE media_id='a4600000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query("SELECT count(*) FROM media.media_item_lifecycle_transition_contexts WHERE media_id='a4600000-0000-4000-8000-000000000402'")->fetchColumn());
        self::assertSame(1, $this->outboxCount());
    }

    private function integrator(PublicProjectionOutboxWriter $writer): MediaItemLifecycleAtomicEventOrchestrator
    {
        return new MediaItemLifecycleAtomicEventOrchestrator($this->orchestrator, $this->inspector, new PostgreSqlAggregateOutboxTransaction($this->pdo), new MediaItemLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $writer, PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
    }

    private function writer(): PostgreSqlPublicProjectionOutboxWriter
    {
        return new PostgreSqlPublicProjectionOutboxWriter($this->pdo, new PostgreSqlPublicProjectionOutboxMapper);
    }

    private function request(MediaItemLifecycleAction $action, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): MediaItemLifecycleAtomicEventRequest
    {
        $id = $this->id();
        $occurred = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:00Z'));
        $context = new MediaItemLifecycleTransitionContext(MediaItemLifecycleContextVersion::V1, MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'), MediaId::fromString($id->value), new MediaItemLifecycleExpectedVersion(1), new MediaCollectionDecisionVersion(7), MediaItemLifecycleActorId::fromString($actor), $occurred, MediaCollectionTransitionDecision::notPrimary());

        return new MediaItemLifecycleAtomicEventRequest($id, $action, $context, MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:01Z')));
    }

    private function id(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000401');
    }

    private function outboxCount(): int
    {
        return (int) $this->pdo->query('SELECT count(*) FROM media.public_projection_outbox_messages')->fetchColumn();
    }
}

final class RejectingMediaItemLifecycleOutboxWriter implements PublicProjectionOutboxWriter
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
