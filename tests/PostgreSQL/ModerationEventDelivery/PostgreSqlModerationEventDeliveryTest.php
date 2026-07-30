<?php

namespace Tests\PostgreSQL\ModerationEventDelivery;

use App\Application\ModerationEventDelivery\ModerationDeliveryResult;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Infrastructure\ModerationEventDelivery\PostgreSql\PostgreSqlModerationDeliveryStore;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationEventDeliveryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationDeliveryStore $store;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->store = new PostgreSqlModerationDeliveryStore($this->connection);
    }

    #[Test]
    public function delivery_replay_divergence_retry_and_quarantine_are_deterministic(): void
    {
        $message = new ModerationDeliveryMessageV1($this->event('Confirmed'));
        $destination = ModerationRoutingDestination::CaseTimeline;
        self::assertSame(ModerationDeliveryResult::Delivered, $this->store->deliver($message, $destination, $this->time()));
        self::assertSame(ModerationDeliveryResult::AlreadyDelivered, $this->store->deliver($message, $destination, $this->time()));
        self::assertSame(ModerationDeliveryResult::DivergentDelivery, $this->store->deliver(
            new ModerationDeliveryMessageV1($this->event('Rejected')), $destination, $this->time(),
        ));

        $retry = new ModerationDeliveryMessageV1($this->event('Retry', 2));
        self::assertSame(ModerationDeliveryResult::Retry, $this->store->fail($retry, $destination, 1, $this->time()));
        self::assertSame(ModerationDeliveryResult::Quarantined, $this->store->fail($retry, $destination, 5, $this->time()));
    }

    #[Test]
    public function savepoint_and_outer_rollback_remove_delivery_completely(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(ModerationDeliveryResult::Delivered, $this->store->deliver(
            new ModerationDeliveryMessageV1($this->event('Rollback', 3)),
            ModerationRoutingDestination::DeliveryObservation,
            $this->time(),
        ));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.event_deliveries')->fetchColumn());
    }

    #[Test]
    public function concurrent_delivery_converges_to_delivered_and_already_delivered(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-delivery-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start delivery worker.');
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
        self::assertSame(['already_delivered', 'delivered'], $results);
    }

    private function event(string $disposition, int $version = 1): ModerationEventV1
    {
        return new ModerationEventV1(
            ModerationEventTypeV1::DecisionIssued, $this->id(1), $version,
            ['decisionId' => $this->id(2), 'disposition' => $disposition],
            'v1', $this->time(), $this->time(), $this->id(3), $this->id(4),
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('53e10000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
