<?php

namespace Tests\PostgreSQL\MediaItemLifecyclePersistence;

use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadStatus;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\MediaItemLifecycleWorkflowStoreContract;

final class PostgreSqlMediaItemLifecycleWorkflowStoreTest extends MediaItemLifecycleWorkflowStoreContract
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        parent::setUp();
    }

    protected function store(): MediaItemLifecycleWorkflowStore
    {
        return new PostgreSqlMediaItemLifecycleWorkflowRepository($this->pdo, new MediaItemLifecycleWorkflowMapper);
    }

    protected function resetStore(): void
    {
        PostgreSqlTestEnvironment::reset($this->pdo);
    }

    public function test_corrupted_row_is_reported_without_exception(): void
    {
        $this->store()->initialize($this->id());
        $this->pdo->exec("UPDATE media.media_item_lifecycle_transitions SET transition_checksum='".str_repeat('0', 64)."'");
        self::assertSame(MediaItemLifecyclePersistenceReadStatus::Corrupted, $this->store()->read($this->id())->status);
    }

    public function test_external_transaction_rollback_removes_all_writes(): void
    {
        $this->pdo->beginTransaction();
        $this->store()->initialize($this->id());
        $this->store()->append($this->id(), $this->remove(), 2);
        $this->pdo->rollBack();
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM media.media_item_lifecycle_transitions')->fetchColumn());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'031_media_item_lifecycle_workflow.down.sql');
        self::assertIsString($down);
        $this->pdo->exec($down);
        self::assertNull($this->pdo->query("SELECT to_regclass('media.media_item_lifecycle_transitions')")->fetchColumn());
        $up = file_get_contents($root.'031_media_item_lifecycle_workflow.sql');
        self::assertIsString($up);
        $this->pdo->exec($up);
        self::assertSame('media.media_item_lifecycle_transitions', $this->pdo->query("SELECT to_regclass('media.media_item_lifecycle_transitions')::text")->fetchColumn());
    }

    public function test_concurrent_identical_appends_converge_without_duplicate(): void
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000020');
        $this->store()->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'media-item-lifecycle-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start media item lifecycle worker.');
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
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM media.media_item_lifecycle_transitions WHERE media_id='a4600000-0000-4000-8000-000000000020'")->fetchColumn());
    }
}
