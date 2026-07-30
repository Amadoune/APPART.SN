<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicProjectionWorkerConcurrencyTest extends TestCase
{
    public function test_two_processes_produce_one_durable_effect_for_one_message(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $connection->exec('CREATE TABLE IF NOT EXISTS listing_lifecycle.worker_test_effects (message_id text PRIMARY KEY)');
        $connection->exec('TRUNCATE listing_lifecycle.worker_test_effects');
        $consumer = PublicProjectionOutboxConsumerId::fromString('public-projection');
        (new PostgreSqlPublicProjectionOutboxWriter($connection, new PostgreSqlPublicProjectionOutboxMapper))->append($this->message(), $consumer);

        $results = $this->runWorkers(10);

        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM listing_lifecycle.worker_test_effects')->fetchColumn());
        self::assertSame(1, (int) $connection->query("SELECT count(*) FROM listing_lifecycle.public_projection_outbox_deliveries WHERE status='delivered'")->fetchColumn());
        self::assertCount(2, $results);
    }

    public function test_two_workers_progress_on_distinct_aggregates_without_a_global_lock(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $connection->exec('CREATE TABLE IF NOT EXISTS listing_lifecycle.worker_test_effects (message_id text PRIMARY KEY)');
        $connection->exec('TRUNCATE listing_lifecycle.worker_test_effects');
        $consumer = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $writer = new PostgreSqlPublicProjectionOutboxWriter($connection, new PostgreSqlPublicProjectionOutboxMapper);
        $writer->append($this->message('42000000-0000-4000-8000-000000000001'), $consumer);
        $writer->append($this->message('42000000-0000-4000-8000-000000000002'), $consumer);

        $results = $this->runWorkers(2);

        self::assertSame(2, (int) $connection->query('SELECT count(*) FROM listing_lifecycle.worker_test_effects')->fetchColumn());
        self::assertSame(2, (int) $connection->query("SELECT count(*) FROM listing_lifecycle.public_projection_outbox_deliveries WHERE status='delivered'")->fetchColumn());
        self::assertSame(['1:2', '1:2'], $results);
    }

    /** @return list<string> */
    private function runWorkers(int $batchSize): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-outbox-worker-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'worker-concurrency-worker.php', $barrier, (string) $number, (string) $batchSize], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Outbox worker process.');
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
                throw new RuntimeException('Outbox worker process failed.');
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }

    private function message(string $id = '42000000-0000-4000-8000-000000000001'): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString($id), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryListingPayload($id));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}
