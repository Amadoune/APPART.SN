<?php

namespace Tests\PostgreSQL\ListingCreation;

use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftCommandV1;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftStatusV1;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\ListingLifecycle\Support\FakePropertyCatalog;

final class PostgreSqlCreateListingDraftV1Test extends TestCase
{
    private PDO $connection;

    private FakePropertyCatalog $properties;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->properties = new FakePropertyCatalog;
        $this->properties->set(PropertyId::fromString($this->command()->propertyId), PropertyAvailability::Eligible);
    }

    #[Test]
    public function applied_replay_and_divergence_are_closed_and_deterministic(): void
    {
        $service = $this->service();
        $command = $this->command();

        self::assertSame(CreateListingDraftStatusV1::Applied, $service->create($command)->status);
        self::assertSame(CreateListingDraftStatusV1::AlreadyApplied, $service->create($command)->status);
        self::assertSame(
            CreateListingDraftStatusV1::DivergentIntent,
            $service->create($this->command(propertyId: '55000000-0000-4000-8000-000000000099'))->status,
        );
        self::assertSame(1, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.listing_revisions'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.listing_creation_intents'));
    }

    #[Test]
    public function listing_id_collision_rolls_back_the_losing_intent(): void
    {
        $service = $this->service();
        self::assertSame(CreateListingDraftStatusV1::Applied, $service->create($this->command())->status);
        self::assertSame(
            CreateListingDraftStatusV1::ListingIdConflict,
            $service->create($this->command(intentId: '55000000-0000-4000-8000-000000000011'))->status,
        );
        self::assertSame(1, $this->tableCount('listing_lifecycle.listing_creation_intents'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.listings'));
    }

    #[Test]
    public function unavailable_property_rolls_back_intent_root_and_revision(): void
    {
        $this->properties = new FakePropertyCatalog;
        $result = $this->service()->create($this->command());

        self::assertSame(CreateListingDraftStatusV1::PropertyUnavailable, $result->status);
        self::assertSame(0, $this->tableCount('listing_lifecycle.listing_creation_intents'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.listing_revisions'));
    }

    #[Test]
    public function an_enclosing_transaction_can_roll_back_intent_listing_and_revision(): void
    {
        $this->connection->beginTransaction();
        try {
            self::assertSame(CreateListingDraftStatusV1::Applied, $this->service()->create($this->command())->status);
            self::assertSame(1, $this->tableCount('listing_lifecycle.listing_creation_intents'));
            self::assertTrue($this->connection->inTransaction());
        } finally {
            $this->connection->rollBack();
        }

        self::assertSame(0, $this->tableCount('listing_lifecycle.listing_creation_intents'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.listings'));
    }

    #[Test]
    public function two_concurrent_identical_commands_converge_once(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'listing-create-v1-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Listing creation worker.');
            }
            $processes[] = [$process, $pipes];
        }

        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Listing creation workers did not reach the barrier.');
        }
        touch($barrier.'.start');

        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            $exit = proc_close($process);
            self::assertSame('', $error);
            self::assertSame(0, $exit);
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        sort($results);
        self::assertSame(['AlreadyApplied', 'Applied'], $results);
        self::assertSame(1, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.listing_creation_intents'));
    }

    private function service(): DeterministicCreateListingDraftV1
    {
        $transaction = new PostgreSqlListingTransaction($this->connection);
        $repository = new PostgreSqlListingRepository($this->connection, new ListingMapper, $transaction);

        return new DeterministicCreateListingDraftV1(
            new CreateDraft($repository, $this->properties, new ListingTransitionPolicy),
            new PostgreSqlListingCreationIntentStore($this->connection),
            $transaction,
        );
    }

    private function command(
        string $intentId = '55000000-0000-4000-8000-000000000001',
        string $propertyId = '55000000-0000-4000-8000-000000000003',
    ): CreateListingDraftCommandV1 {
        return new CreateListingDraftCommandV1(
            $intentId,
            '55000000-0000-4000-8000-000000000002',
            $propertyId,
            '55000000-0000-4000-8000-000000000004',
            'account:owner',
            new DateTimeImmutable('2026-07-27T14:00:00+00:00'),
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
