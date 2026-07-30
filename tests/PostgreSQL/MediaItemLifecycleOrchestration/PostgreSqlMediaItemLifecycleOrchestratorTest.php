<?php

namespace Tests\PostgreSQL\MediaItemLifecycleOrchestration;

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
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;
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
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlMediaItemLifecycleOrchestratorTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlMediaItemLifecycleWorkflowRepository $historical;

    private DeterministicMediaItemLifecycleOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $workflowMapper = new MediaItemLifecycleWorkflowMapper;
        $this->historical = new PostgreSqlMediaItemLifecycleWorkflowRepository($this->connection, $workflowMapper);
        $store = new PostgreSqlMediaItemLifecycleContextualTransitionRepository($this->connection, $this->historical, $workflowMapper, new MediaItemLifecycleContextMapper);
        $inspector = new PostgreSqlMediaItemLifecycleContextualReplayInspector($this->connection, $workflowMapper);
        $this->orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, $inspector, new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);
    }

    public function test_nominal_replay_divergence_and_denial_follow_the_certified_sequence(): void
    {
        $id = $this->id();
        $this->historical->initialize($id);
        self::assertSame(MediaItemLifecycleOrchestrationStatus::Applied, $this->orchestrator->execute($this->request($id))->status);
        self::assertSame(MediaItemLifecycleOrchestrationStatus::AlreadyApplied, $this->orchestrator->execute($this->request($id))->status);
        self::assertSame(MediaItemLifecycleOrchestrationStatus::ContextDivergence, $this->orchestrator->execute($this->request($id, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'))->status);
        self::assertSame(2, $this->transitionCount($id));
        self::assertSame(1, $this->contextCount($id));

        $other = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000048');
        $this->historical->initialize($other);
        $denied = new MediaItemLifecycleTransitionRequest($other, MediaItemLifecycleAction::Unknown, $this->context($other));
        self::assertSame(MediaItemLifecycleOrchestrationStatus::Denied, $this->orchestrator->execute($denied)->status);
        self::assertSame(1, $this->transitionCount($other));
        self::assertSame(0, $this->contextCount($other));
    }

    public function test_multiprocess_identical_and_divergent_commands_converge_without_partial_state(): void
    {
        self::assertSame(['already_applied', 'applied'], $this->concurrentResults(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']));
        $this->assertSingleAppend();

        PostgreSqlTestEnvironment::reset($this->connection);
        self::assertSame(['applied', 'context_divergence'], $this->concurrentResults(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb']));
        $this->assertSingleAppend();
    }

    /** @param list<string> $actors
     * @return list<string>
     */
    private function concurrentResults(array $actors): array
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000049');
        $this->historical->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'media-item-orchestration-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ($actors as $index => $actor) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) ($index + 1), $actor], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start media item orchestration worker.');
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

        return $results;
    }

    private function assertSingleAppend(): void
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000049');
        self::assertSame(2, $this->transitionCount($id));
        self::assertSame(1, $this->contextCount($id));
    }

    private function transitionCount(MediaItemLifecycleId $id): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM media.media_item_lifecycle_transitions WHERE media_id='".$id->value."'")->fetchColumn();
    }

    private function contextCount(MediaItemLifecycleId $id): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM media.media_item_lifecycle_transition_contexts WHERE media_id='".$id->value."'")->fetchColumn();
    }

    private function request(MediaItemLifecycleId $id, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): MediaItemLifecycleTransitionRequest
    {
        return new MediaItemLifecycleTransitionRequest($id, MediaItemLifecycleAction::Remove, $this->context($id, $actor));
    }

    private function context(MediaItemLifecycleId $id, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): MediaItemLifecycleTransitionContext
    {
        return new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
            MediaId::fromString($id->value),
            new MediaItemLifecycleExpectedVersion(1),
            new MediaCollectionDecisionVersion(7),
            MediaItemLifecycleActorId::fromString($actor),
            MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00.123456Z')),
            MediaCollectionTransitionDecision::notPrimary(),
        );
    }

    private function id(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000047');
    }
}
