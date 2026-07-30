<?php

namespace Tests\PostgreSQL\ListingModerationIntentSource;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingModerationIntentSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlListingModerationIntentStore $store;

    private PostgreSqlListingModerationIntentTransaction $transaction;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->store = new PostgreSqlListingModerationIntentStore($this->connection);
        $this->transaction = new PostgreSqlListingModerationIntentTransaction($this->connection);
    }

    #[Test]
    public function intent_is_durable_idempotent_divergence_safe_and_append_only(): void
    {
        $commandId = $this->id(1);
        $checksum = str_repeat('a', 64);
        $this->transaction->run(function () use ($commandId, $checksum): void {
            self::assertSame(
                ListingModerationIntentReservationV1::Reserved,
                $this->store->reserve($commandId, $checksum, $this->now()),
            );
            self::assertTrue($this->store->complete(
                $commandId,
                $checksum,
                ListingModerationCommandResultV1::Applied,
                $this->now(),
            ));
        });

        self::assertSame(
            ListingModerationIntentReservationV1::AlreadyApplied,
            $this->transaction->run(
                fn () => $this->store->reserve($commandId, $checksum, $this->now()),
            ),
        );
        self::assertSame(
            ListingModerationIntentReservationV1::DivergentIntent,
            $this->transaction->run(
                fn () => $this->store->reserve($commandId, str_repeat('b', 64), $this->now()),
            ),
        );
        self::assertSame(ListingModerationCommandResultV1::Applied, $this->store->find($commandId)?->terminalResult);
        self::assertSame(1, $this->tableCount('moderation_command_intents'));
        self::assertSame(1, $this->tableCount('moderation_command_intent_results'));
    }

    #[Test]
    public function intent_and_f01_transition_commit_and_rollback_in_one_local_transaction(): void
    {
        $repository = new PostgreSqlListingPublicationWorkflowRepository(
            $this->connection,
            new ListingPublicationWorkflowMapper,
        );
        $listingId = ListingId::fromString($this->id(2));
        $repository->initialize($listingId, ListingPublicationState::Published);
        $orchestrator = new DeterministicListingPublicationOrchestrator(
            new ListingPublicationWorkflow,
            $repository,
        );
        $commandId = $this->id(3);
        $checksum = str_repeat('c', 64);

        $this->transaction->run(function () use ($repository, $orchestrator, $listingId, $commandId, $checksum): void {
            self::assertSame(
                ListingModerationIntentReservationV1::Reserved,
                $this->store->reserve($commandId, $checksum, $this->now()),
            );
            $result = $orchestrator->transition(new ListingPublicationOrchestrationRequest(
                $listingId,
                ListingPublicationAction::Suspend,
                1,
            ));
            self::assertSame('applied', $result->status->value);
            self::assertTrue($this->store->complete(
                $commandId,
                $checksum,
                ListingModerationCommandResultV1::Applied,
                $this->now(),
            ));
            self::assertSame(ListingPublicationState::Suspended, $repository->read($listingId)->snapshot?->state);
        });

        $this->connection->beginTransaction();
        $this->transaction->run(function (): void {
            $this->store->reserve($this->id(4), str_repeat('d', 64), $this->now());
        });
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertNull($this->store->find($this->id(4)));
    }

    #[Test]
    public function migration_has_complete_rollback_and_no_cross_domain_constraints(): void
    {
        $migration = dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($migration.'068_listing_moderation_intents.down.sql'));
        self::assertFalse((bool) $this->connection->query(
            "SELECT to_regclass('listing_lifecycle.moderation_command_intents') IS NOT NULL",
        )->fetchColumn());
        $this->connection->exec((string) file_get_contents($migration.'068_listing_moderation_intents.sql'));
        self::assertTrue((bool) $this->connection->query(
            "SELECT to_regclass('listing_lifecycle.moderation_command_intents') IS NOT NULL",
        )->fetchColumn());
    }

    #[Test]
    public function concurrent_same_intent_and_transition_converge_deterministically(): void
    {
        $repository = new PostgreSqlListingPublicationWorkflowRepository(
            $this->connection,
            new ListingPublicationWorkflowMapper,
        );
        $repository->initialize(
            ListingId::fromString('53c30000-0000-4000-8000-000000000002'),
            ListingPublicationState::Published,
        );
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'listing-moderation-intent-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Listing moderation intent worker.');
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
            self::assertSame('', trim(stream_get_contents($pipes[2])));
            self::assertSame(0, proc_close($process));
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        sort($results);
        self::assertSame(['already_applied', 'applied'], $results);
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query(
            "SELECT count(*) FROM listing_lifecycle.{$table}",
        )->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53c30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
