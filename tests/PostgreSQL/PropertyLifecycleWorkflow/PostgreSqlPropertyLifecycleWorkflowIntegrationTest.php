<?php

namespace Tests\PostgreSQL\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceReadStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyLifecycleWorkflowRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyLifecycleWorkflowIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_checksum_corruption_is_an_explicit_read_result(): void
    {
        $id = $this->propertyId();
        $this->repository()->initialize($id, PropertyLifecycleState::Draft);
        $this->connection->exec("UPDATE real_estate_catalog.property_lifecycle_transitions SET transition_checksum='".str_repeat('0', 64)."'");

        self::assertSame(PropertyLifecyclePersistenceReadStatus::Corrupted, $this->repository()->read($id)->status);
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $id = $this->propertyId();
        $this->connection->beginTransaction();
        $this->repository()->initialize($id, PropertyLifecycleState::Draft);
        $this->connection->rollBack();

        self::assertSame(PropertyLifecyclePersistenceReadStatus::Missing, $this->repository()->read($id)->status);
    }

    public function test_current_state_read_is_bounded_and_uses_the_dedicated_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT current_state FROM real_estate_catalog.property_lifecycle_transitions WHERE property_id=:property_id ORDER BY version DESC LIMIT 1');
        $statement->execute(['property_id' => $this->propertyId()->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));

        self::assertStringContainsString('property_lifecycle_current_state_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'017_property_lifecycle_workflow.down.sql');
        $up = file_get_contents($root.'017_property_lifecycle_workflow.sql');
        self::assertIsString($down);
        self::assertIsString($up);
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('real_estate_catalog.property_lifecycle_transitions')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('real_estate_catalog.property_lifecycle_transitions', $this->connection->query("SELECT to_regclass('real_estate_catalog.property_lifecycle_transitions')")->fetchColumn());
    }

    public function test_concurrent_identical_initializations_converge_without_duplicate_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-property-lifecycle-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start property lifecycle worker.');
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
                throw new RuntimeException('Property lifecycle worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM real_estate_catalog.property_lifecycle_transitions WHERE property_id='97100000-0000-4000-8000-000000000099'")->fetchColumn());
    }

    private function repository(): PostgreSqlPropertyLifecycleWorkflowRepository
    {
        return new PostgreSqlPropertyLifecycleWorkflowRepository($this->connection, new PropertyLifecycleWorkflowMapper);
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('97100000-0000-4000-8000-000000000001');
    }
}
