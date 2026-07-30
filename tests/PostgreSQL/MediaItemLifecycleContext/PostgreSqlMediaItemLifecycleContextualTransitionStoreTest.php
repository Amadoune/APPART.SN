<?php

namespace Tests\PostgreSQL\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use PDO;
use PDOException;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\MediaItemLifecycleContextualTransitionStoreContract;

final class PostgreSqlMediaItemLifecycleContextualTransitionStoreTest extends MediaItemLifecycleContextualTransitionStoreContract
{
    private PDO $connection;

    private MediaItemLifecycleWorkflowStore $historical;

    private MediaItemLifecycleContextualTransitionStore $store;

    private MediaItemLifecycleContextualReplayInspector $inspector;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $workflowMapper = new MediaItemLifecycleWorkflowMapper;
        $this->historical = new PostgreSqlMediaItemLifecycleWorkflowRepository($this->connection, $workflowMapper);
        $this->store = new PostgreSqlMediaItemLifecycleContextualTransitionRepository($this->connection, $this->historical, $workflowMapper, new MediaItemLifecycleContextMapper);
        $this->inspector = new PostgreSqlMediaItemLifecycleContextualReplayInspector($this->connection, $workflowMapper);
    }

    public function test_all_conflict_results_are_closed(): void
    {
        $missing = $this->contractAppend(MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000033'));
        self::assertSame(MediaItemLifecycleContextualWriteResult::VersionConflict, $this->store->append($missing));

        $id = $this->contractId();
        $this->seedActive($id);
        $wrongState = new MediaItemLifecycleContextualAppend(
            $id,
            new MediaItemLifecycleTransition(MediaItemLifecycleState::Removed, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive),
            $this->contractAppend($id)->context,
        );
        self::assertSame(MediaItemLifecycleContextualWriteResult::StateConflict, $this->store->append($wrongState));

        $rejected = new MediaItemLifecycleContextualAppend(
            $id,
            new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Active, MediaItemLifecycleAction::Remove),
            $this->contractAppend($id)->context,
        );
        self::assertSame(MediaItemLifecycleContextualWriteResult::TransitionRejected, $this->store->append($rejected));
    }

    public function test_inspection_restores_exact_context_and_detects_corruption(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $append = $this->contractAppend($id);
        $this->store->append($append);

        $found = $this->inspector->inspectLatest($id);
        self::assertSame(MediaItemLifecycleContextualInspectionStatus::Found, $found->status);
        self::assertEquals($append->transition, $found->snapshot?->transition);
        self::assertEquals($append->context, $found->snapshot?->context);
        self::assertSame($append->context->checksum()->value, $found->snapshot?->checksum->value);

        $this->connection->exec("UPDATE media.media_item_lifecycle_transition_contexts SET context_checksum='".str_repeat('f', 64)."' WHERE media_id='".$id->value."'");
        self::assertSame(MediaItemLifecycleContextualInspectionStatus::Corrupted, $this->inspector->inspectLatest($id)->status);
    }

    public function test_replay_reports_corrupted_when_the_persisted_context_is_missing(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $append = $this->contractAppend($id);
        self::assertSame(MediaItemLifecycleContextualWriteResult::Applied, $this->store->append($append));
        $this->connection->exec("DELETE FROM media.media_item_lifecycle_transition_contexts WHERE media_id='".$id->value."'");

        self::assertSame(MediaItemLifecycleContextualWriteResult::Corrupted, $this->store->append($append));
    }

    public function test_missing_inspection_is_explicit(): void
    {
        self::assertSame(MediaItemLifecycleContextualInspectionStatus::Missing, $this->inspector->inspectLatest($this->contractId())->status);
    }

    public function test_external_transaction_rolls_back_transition_and_context(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $this->connection->beginTransaction();
        $this->store->append($this->contractAppend($id));
        $this->connection->rollBack();

        self::assertSame(1, $this->rowCount('media.media_item_lifecycle_transitions', $id));
        self::assertSame(0, $this->rowCount('media.media_item_lifecycle_transition_contexts', $id));
    }

    public function test_local_transaction_rolls_back_transition_when_context_insert_fails(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $this->connection->exec("CREATE OR REPLACE FUNCTION media.reject_lifecycle_context() RETURNS trigger LANGUAGE plpgsql AS 'BEGIN RAISE EXCEPTION ''forced context failure''; END'");
        $this->connection->exec('CREATE TRIGGER reject_lifecycle_context BEFORE INSERT ON media.media_item_lifecycle_transition_contexts FOR EACH ROW EXECUTE FUNCTION media.reject_lifecycle_context()');

        try {
            $this->store->append($this->contractAppend($id));
            self::fail('The forced context failure must propagate.');
        } catch (PDOException) {
            self::assertSame(1, $this->rowCount('media.media_item_lifecycle_transitions', $id));
            self::assertSame(0, $this->rowCount('media.media_item_lifecycle_transition_contexts', $id));
        } finally {
            $this->connection->exec('DROP TRIGGER IF EXISTS reject_lifecycle_context ON media.media_item_lifecycle_transition_contexts');
            $this->connection->exec('DROP FUNCTION IF EXISTS media.reject_lifecycle_context()');
        }
    }

    public function test_migration_032_and_rollback_are_isolated_from_031(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'032_media_item_lifecycle_context.down.sql'));
        self::assertSame('media.media_item_lifecycle_transitions', $this->connection->query("SELECT to_regclass('media.media_item_lifecycle_transitions')::text")->fetchColumn());
        self::assertNull($this->connection->query("SELECT to_regclass('media.media_item_lifecycle_transition_contexts')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'032_media_item_lifecycle_context.sql'));
        self::assertSame('media.media_item_lifecycle_transition_contexts', $this->connection->query("SELECT to_regclass('media.media_item_lifecycle_transition_contexts')::text")->fetchColumn());
    }

    public function test_concurrent_identical_and_divergent_appends_are_deterministic(): void
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
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000034');
        $this->seedActive($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'media-item-context-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ($actors as $index => $actor) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) ($index + 1), $actor], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start contextual media item worker.');
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
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000034');
        self::assertSame(2, $this->rowCount('media.media_item_lifecycle_transitions', $id));
        self::assertSame(1, $this->rowCount('media.media_item_lifecycle_transition_contexts', $id));
    }

    private function rowCount(string $table, MediaItemLifecycleId $id): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM $table WHERE media_id='".$id->value."'")->fetchColumn();
    }

    protected function contextualStore(): MediaItemLifecycleContextualTransitionStore
    {
        return $this->store;
    }

    protected function seedActive(MediaItemLifecycleId $mediaId): void
    {
        $this->historical->initialize($mediaId);
    }
}
