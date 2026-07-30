<?php

namespace Tests\PostgreSQL\ModerationListingHandoffOutboxRouting;

use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationListingHandoffOutboxRoutingTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationOutbox $outbox;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->outbox = new PostgreSqlModerationOutbox(
            $this->connection,
            new ModerationEventTransportSerializer,
            new DeterministicModerationEventRouter,
        );
    }

    #[Test]
    public function filtered_claim_never_claims_a_foreign_destination(): void
    {
        $report = $this->message(ModerationEventTypeV1::ReportSubmitted, 1);
        $decision = $this->message(ModerationEventTypeV1::DecisionIssued, 2);
        self::assertSame(ModerationOutboxAppendResult::Stored, $this->outbox->append($report));
        self::assertSame(ModerationOutboxAppendResult::Stored, $this->outbox->append($decision));

        $claimed = $this->outbox->claimNextForDestination(
            'listing-worker',
            ModerationRoutingDestination::ListingHandoff,
            $this->now(),
        );

        self::assertNotNull($claimed);
        self::assertSame($decision->messageId, $claimed->message->messageId);
        self::assertSame(ModerationRoutingDestination::ListingHandoff, $claimed->destination);
        self::assertNull($this->outbox->read(
            $report->messageId,
            ModerationRoutingDestination::ListingHandoff->value,
        ));
    }

    #[Test]
    public function lease_retry_replay_and_quarantine_remain_destination_scoped(): void
    {
        $message = $this->message(ModerationEventTypeV1::DecisionIssued, 3);
        $this->outbox->append($message);
        $first = $this->outbox->claimNextForDestination(
            'listing-a',
            ModerationRoutingDestination::ListingHandoff,
            $this->now(),
        );
        self::assertNotNull($first);
        self::assertNull($this->outbox->claimNextForDestination(
            'listing-b',
            ModerationRoutingDestination::ListingHandoff,
            $this->now()->modify('+29 seconds'),
        ));
        $expired = $this->outbox->claimNextForDestination(
            'listing-b',
            ModerationRoutingDestination::ListingHandoff,
            $this->now()->modify('+30 seconds'),
        );
        self::assertNotNull($expired);
        self::assertTrue($this->outbox->retry($expired, $this->now()->modify('+31 seconds'), 'temporary'));
        $retried = $this->outbox->claimNextForDestination(
            'listing-c',
            ModerationRoutingDestination::ListingHandoff,
            $this->now()->modify('+31 seconds'),
        );
        self::assertNotNull($retried);
        self::assertSame(ModerationRoutingDestination::ListingHandoff, $retried->destination);
        self::assertTrue($this->outbox->quarantine($retried, 'corrupted'));
        self::assertTrue($this->outbox->replay(
            $message->messageId,
            ModerationRoutingDestination::ListingHandoff->value,
            $this->now()->modify('+32 seconds'),
        ));
        $replayed = $this->outbox->claimNextForDestination(
            'listing-d',
            ModerationRoutingDestination::ListingHandoff,
            $this->now()->modify('+32 seconds'),
        );
        self::assertNotNull($replayed);
        self::assertTrue($this->outbox->markDelivered($replayed, $this->now()->modify('+33 seconds')));
    }

    #[Test]
    public function historical_unfiltered_claim_remains_compatible(): void
    {
        $message = $this->message(ModerationEventTypeV1::ReportSubmitted, 4);
        $this->outbox->append($message);

        $claimed = $this->outbox->claimNext('historical-worker', $this->now());

        self::assertNotNull($claimed);
        self::assertSame($message->messageId, $claimed->message->messageId);
        self::assertContains($claimed->destination, [
            ModerationRoutingDestination::QueueProjection,
            ModerationRoutingDestination::CaseTimeline,
        ]);
    }

    #[Test]
    public function concurrent_listing_workers_claim_distinct_messages_without_duplication(): void
    {
        $first = $this->message(ModerationEventTypeV1::DecisionIssued, 5);
        $second = $this->message(ModerationEventTypeV1::DecisionIssued, 6);
        $this->outbox->append($first);
        $this->outbox->append($second);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-listing-route-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start filtered Outbox worker.');
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

        self::assertCount(2, array_unique($results));
        self::assertEqualsCanonicalizing([$first->messageId, $second->messageId], $results);
    }

    private function message(ModerationEventTypeV1 $type, int $suffix): ModerationDeliveryMessageV1
    {
        return new ModerationDeliveryMessageV1(new ModerationEventV1(
            $type,
            $this->id($suffix),
            1,
            ['decisionId' => $this->id($suffix + 100)],
            'v1',
            $this->now(),
            $this->now(),
            $this->id($suffix + 200),
            $this->id($suffix + 300),
        ));
    }

    private function id(int $suffix): string
    {
        return sprintf('53e30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
