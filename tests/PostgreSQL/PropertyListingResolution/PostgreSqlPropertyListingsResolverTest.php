<?php

namespace Tests\PostgreSQL\PropertyListingResolution;

use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;
use App\Infrastructure\PropertyListingResolution\PostgreSql\PostgreSqlPropertyListingsResolver;
use App\Infrastructure\PropertyListingResolution\PropertyListingsCheckpoint;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyListingsResolverTest extends TestCase
{
    private const string PROPERTY = '9b000000-0000-4000-8000-000000000001';

    private const string OTHER_PROPERTY = '9b000000-0000-4000-8000-000000000002';

    private const string A = '9a000000-0000-4000-8000-000000000001';

    private const string B = '9a000000-0000-4000-8000-000000000002';

    private const string C = '9a000000-0000-4000-8000-000000000003';

    private PDO $connection;

    private PostgreSqlPropertyListingsResolver $resolver;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->resolver = new PostgreSqlPropertyListingsResolver($this->connection, new PropertyListingsCheckpoint);
    }

    public function test_zero_one_and_multiple_cardinalities_are_explicit(): void
    {
        self::assertSame(PropertyListingsPageStatus::Empty, $this->resolver->readPage(self::PROPERTY, null, 10)->status);
        $this->insert(self::B, self::PROPERTY);
        $one = $this->resolver->readPage(self::PROPERTY, null, 10);
        self::assertSame(PropertyListingsPageStatus::Completed, $one->status);
        self::assertSame([self::B], $one->listingIds);
        $this->insert(self::A, self::PROPERTY);
        $this->insert(self::C, self::PROPERTY);
        self::assertSame([self::A, self::B, self::C], $this->resolver->readPage(self::PROPERTY, null, 10)->listingIds);
    }

    public function test_keyset_pages_are_ordered_replayable_resumable_and_exhaustive(): void
    {
        foreach ([self::C, self::A, self::B] as $id) {
            $this->insert($id, self::PROPERTY);
        }
        $first = $this->resolver->readPage(self::PROPERTY, null, 2);
        self::assertSame(PropertyListingsPageStatus::Found, $first->status);
        self::assertSame([self::A, self::B], $first->listingIds);
        self::assertNotNull($first->nextCheckpoint);
        self::assertEquals($first, $this->resolver->readPage(self::PROPERTY, null, 2));

        $last = $this->resolver->readPage(self::PROPERTY, $first->nextCheckpoint, 2);
        self::assertSame(PropertyListingsPageStatus::Completed, $last->status);
        self::assertSame([self::C], $last->listingIds);
        self::assertTrue($last->completed);
    }

    public function test_other_property_is_never_returned_and_checkpoint_is_property_bound(): void
    {
        $this->insert(self::A, self::PROPERTY);
        $this->insert(self::B, self::PROPERTY);
        $this->insert(self::C, self::OTHER_PROPERTY);
        $first = $this->resolver->readPage(self::PROPERTY, null, 1);
        self::assertSame([self::A], $first->listingIds);
        self::assertNotNull($first->nextCheckpoint);
        $wrong = $this->resolver->readPage(self::OTHER_PROPERTY, $first->nextCheckpoint, 1);
        self::assertSame(PropertyListingsPageStatus::Corrupted, $wrong->status);
        self::assertSame(PropertyListingsDiagnostic::CheckpointForAnotherProperty, $wrong->diagnostic);
    }

    public function test_invalid_identity_checkpoint_and_limit_are_diagnostic_results(): void
    {
        self::assertSame(PropertyListingsPageStatus::InvalidIdentity, $this->resolver->readPage('invalid', null, 1)->status);
        self::assertSame(PropertyListingsDiagnostic::InvalidCheckpoint, $this->resolver->readPage(self::PROPERTY, 'invalid', 1)->diagnostic);
        self::assertSame(PropertyListingsDiagnostic::InvalidLimit, $this->resolver->readPage(self::PROPERTY, null, 0)->diagnostic);
    }

    public function test_reads_are_repeatable_and_never_mutate_durable_state(): void
    {
        $this->insert(self::A, self::PROPERTY);
        $before = (int) $this->connection->query('SELECT COUNT(*) FROM listing_lifecycle.listings')->fetchColumn();
        $expected = $this->resolver->readPage(self::PROPERTY, null, 10);
        for ($iteration = 0; $iteration < 10; $iteration++) {
            self::assertEquals($expected, $this->resolver->readPage(self::PROPERTY, null, 10));
        }
        self::assertSame($before, (int) $this->connection->query('SELECT COUNT(*) FROM listing_lifecycle.listings')->fetchColumn());
    }

    public function test_concurrent_reads_are_deterministic_and_non_mutating(): void
    {
        foreach ([self::C, self::A, self::B] as $id) {
            $this->insert($id, self::PROPERTY);
        }
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-property-listings-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-reader.php', $barrier, (string) $number, self::PROPERTY], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Property-to-Listings reader.');
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
                throw new RuntimeException('Property-to-Listings reader failed: '.$error);
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        $expected = json_encode([self::A, self::B, self::C], JSON_THROW_ON_ERROR);
        self::assertSame([$expected, $expected], $results);
        self::assertSame(3, (int) $this->connection->query('SELECT COUNT(*) FROM listing_lifecycle.listings')->fetchColumn());
    }

    public function test_composite_index_supports_the_bounded_query(): void
    {
        $indexes = $this->connection->query("SELECT indexdef FROM pg_indexes WHERE schemaname='listing_lifecycle' AND indexname='listings_property_id_id_idx'")->fetchAll(PDO::FETCH_COLUMN);
        self::assertCount(1, $indexes);
        self::assertStringContainsString('(property_id, id)', (string) $indexes[0]);
    }

    private function insert(string $listingId, string $propertyId): void
    {
        $statement = $this->connection->prepare("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES (:id,:property,'draft','2026-07-19T10:00:00+00:00',0,0)");
        $statement->execute(['id' => $listingId, 'property' => $propertyId]);
    }
}
