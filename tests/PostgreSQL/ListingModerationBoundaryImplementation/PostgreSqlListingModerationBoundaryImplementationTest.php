<?php

namespace Tests\PostgreSQL\ListingModerationBoundaryImplementation;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentStateV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
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

final class PostgreSqlListingModerationBoundaryImplementationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlListingPublicationWorkflowRepository $workflows;

    private PostgreSqlListingModerationIntentStore $intents;

    private OwnerListingModerationCommandGatewayV1 $gateway;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->workflows = new PostgreSqlListingPublicationWorkflowRepository(
            $this->connection,
            new ListingPublicationWorkflowMapper,
        );
        $this->intents = new PostgreSqlListingModerationIntentStore($this->connection);
        $this->gateway = new OwnerListingModerationCommandGatewayV1(
            $this->intents,
            new PostgreSqlListingModerationIntentTransaction($this->connection),
            new DeterministicListingPublicationOrchestrator(
                new ListingPublicationWorkflow,
                $this->workflows,
            ),
            $this->workflows,
        );
    }

    #[Test]
    public function reader_and_gateway_execute_closed_owner_results_and_durable_idempotence(): void
    {
        $published = ListingId::fromString($this->id(1));
        $archived = ListingId::fromString($this->id(2));
        $this->workflows->initialize($published, ListingPublicationState::Published);
        $this->workflows->initialize($archived, ListingPublicationState::Archived);
        $reader = new OwnerListingModerationReaderV1($this->workflows, new ListingPublicationWorkflow);

        self::assertSame(ListingModerationEligibilityV1::Eligible, $reader->read($published, $this->now()));
        self::assertSame(ListingModerationEligibilityV1::Ineligible, $reader->read($archived, $this->now()));

        $commandId = $this->id(3);
        $checksum = str_repeat('a', 64);
        self::assertSame(
            ListingModerationCommandResultV1::Applied,
            $this->gateway->apply(
                $published,
                ListingModerationActionV1::Suspend,
                $commandId,
                $checksum,
                $this->now(),
            ),
        );
        self::assertSame(
            ListingModerationCommandResultV1::AlreadyApplied,
            $this->gateway->apply(
                $published,
                ListingModerationActionV1::Suspend,
                $commandId,
                $checksum,
                $this->now(),
            ),
        );
        self::assertSame(
            ListingModerationCommandResultV1::DivergentIntent,
            $this->gateway->apply(
                $published,
                ListingModerationActionV1::Suspend,
                $commandId,
                str_repeat('b', 64),
                $this->now(),
            ),
        );
        self::assertSame(ListingPublicationState::Suspended, $this->workflows->read($published)->snapshot?->state);
    }

    #[Test]
    public function owner_transaction_supports_savepoint_and_outer_rollback(): void
    {
        $listingId = ListingId::fromString($this->id(4));
        $commandId = $this->id(5);
        $this->workflows->initialize($listingId, ListingPublicationState::Published);

        $this->connection->beginTransaction();
        self::assertSame(
            ListingModerationCommandResultV1::Applied,
            $this->gateway->apply(
                $listingId,
                ListingModerationActionV1::Suspend,
                $commandId,
                str_repeat('c', 64),
                $this->now(),
            ),
        );
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame(ListingPublicationState::Published, $this->workflows->read($listingId)->snapshot?->state);
        self::assertNull($this->intents->find($commandId));
    }

    #[Test]
    public function completion_failure_rolls_back_intent_and_transition(): void
    {
        $listingId = ListingId::fromString($this->id(6));
        $this->workflows->initialize($listingId, ListingPublicationState::Published);
        $commandId = $this->id(7);
        $failingIntents = new class($this->intents) implements ListingModerationIntentStore
        {
            public function __construct(private PostgreSqlListingModerationIntentStore $delegate) {}

            public function reserve(string $commandId, string $checksum, DateTimeImmutable $recordedAt): ListingModerationIntentReservationV1
            {
                return $this->delegate->reserve($commandId, $checksum, $recordedAt);
            }

            public function find(string $commandId): ?ListingModerationIntentStateV1
            {
                return $this->delegate->find($commandId);
            }

            public function complete(string $commandId, string $checksum, ListingModerationCommandResultV1 $result, DateTimeImmutable $recordedAt): bool
            {
                return false;
            }
        };
        $gateway = new OwnerListingModerationCommandGatewayV1(
            $failingIntents,
            new PostgreSqlListingModerationIntentTransaction($this->connection),
            new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $this->workflows),
            $this->workflows,
        );

        self::assertSame(
            ListingModerationCommandResultV1::DependencyUnavailable,
            $gateway->apply(
                $listingId,
                ListingModerationActionV1::Suspend,
                $commandId,
                str_repeat('d', 64),
                $this->now(),
            ),
        );
        self::assertSame(ListingPublicationState::Published, $this->workflows->read($listingId)->snapshot?->state);
        self::assertNull($this->intents->find($commandId));
    }

    #[Test]
    public function concurrent_same_command_converges_to_one_applied_and_one_already_applied(): void
    {
        $listingId = ListingId::fromString('53c30000-0000-4000-8000-000000000010');
        $this->workflows->initialize($listingId, ListingPublicationState::Published);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'listing-moderation-boundary-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Listing moderation boundary worker.');
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

    private function id(int $suffix): string
    {
        return sprintf('53c30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
