<?php

namespace Tests\PostgreSQL\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingPublicationWorkflowIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_checksum_corruption_is_explicit(): void
    {
        $id = $this->listingId();
        $this->repository()->initialize($id, ListingPublicationState::Draft);
        $this->connection->exec("UPDATE listing_lifecycle.publication_workflow_transitions SET transition_checksum='".str_repeat('0', 64)."'");

        self::assertSame(ListingPublicationPersistenceReadStatus::Corrupted, $this->repository()->read($id)->status);
    }

    public function test_certified_orchestration_applies_workflow_decision_to_postgresql(): void
    {
        $id = $this->listingId();
        $repository = $this->repository();
        $repository->initialize($id, ListingPublicationState::Draft);
        $orchestrator = new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $repository);

        $result = $orchestrator->transition(new ListingPublicationOrchestrationRequest($id, ListingPublicationAction::Submit, 1));
        $stored = $repository->read($id);

        self::assertSame(ListingPublicationOrchestrationStatus::Applied, $result->status);
        self::assertSame(ListingPublicationState::Submitted, $stored->snapshot?->state);
        self::assertSame(2, $stored->snapshot?->version);
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $id = $this->listingId();
        $this->connection->beginTransaction();
        $this->repository()->initialize($id, ListingPublicationState::Draft);
        $this->connection->rollBack();

        self::assertSame(ListingPublicationPersistenceReadStatus::Missing, $this->repository()->read($id)->status);
    }

    public function test_lookup_uses_bounded_current_state_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT current_state FROM listing_lifecycle.publication_workflow_transitions WHERE listing_id=:listing_id ORDER BY version DESC LIMIT 1');
        $statement->execute(['listing_id' => $this->listingId()->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));
        self::assertStringContainsString('publication_workflow_current_state_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'015_listing_publication_workflow.down.sql');
        $up = file_get_contents($root.'015_listing_publication_workflow.sql');
        self::assertIsString($down);
        self::assertIsString($up);
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('listing_lifecycle.publication_workflow_transitions')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('listing_lifecycle.publication_workflow_transitions', $this->connection->query("SELECT to_regclass('listing_lifecycle.publication_workflow_transitions')")->fetchColumn());
    }

    public function test_concurrent_identical_initializations_converge_without_double_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-listing-publication-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start listing publication workflow worker.');
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
                throw new RuntimeException('Listing publication workflow worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM listing_lifecycle.publication_workflow_transitions WHERE listing_id='98100000-0000-4000-8000-000000000099'")->fetchColumn());
    }

    private function repository(): PostgreSqlListingPublicationWorkflowRepository
    {
        return new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper);
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('98100000-0000-4000-8000-000000000001');
    }
}
