<?php

namespace Tests\PostgreSQL\PublicationReview;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingStatus;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewClaimStatus;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewIngestionResult;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItemState;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueReadStatus;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandStatus;
use Appart\Modules\PublicationReview\Infrastructure\Persistence\PostgreSql\PostgreSqlPublicationReviewQueue;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicationReviewQueueTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPublicationReviewQueue $queue;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->queue = new PostgreSqlPublicationReviewQueue($this->connection);
    }

    public function test_queue_ingestion_pagination_claim_and_replay_are_deterministic(): void
    {
        $first = $this->event('11111111-1111-4111-8111-111111111111', 2, '2026-08-10T10:00:00.000000Z');
        $second = $this->event('22222222-2222-4222-8222-222222222222', 2, '2026-08-10T10:01:00.000000Z');
        self::assertSame(PublicationReviewIngestionResult::Applied, $this->queue->ingest($first));
        self::assertSame(PublicationReviewIngestionResult::AlreadyApplied, $this->queue->ingest($first));
        self::assertSame(PublicationReviewIngestionResult::Applied, $this->queue->ingest($second));

        $page = $this->queue->read(null, 1);
        self::assertSame(PublicationReviewQueueReadStatus::Available, $page->status);
        self::assertCount(1, $page->items);
        self::assertNotNull($page->nextCursor);
        self::assertSame($second->eventId->value, $this->queue->read($page->nextCursor, 1)->items[0]->queueItemId);

        $command = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $at = new DateTimeImmutable('2026-08-10T11:00:00+00:00');
        $claimed = $this->queue->claim($command, $first->eventId->value, 'reviewer-1', $at, 1);
        self::assertSame(PublicationReviewClaimStatus::Applied, $claimed->status);
        self::assertSame(2, $claimed->item?->version);
        self::assertTrue($this->queue->hasRecorded($command));
        self::assertSame(PublicationReviewClaimStatus::AlreadyApplied, $this->queue->claim($command, $first->eventId->value, 'reviewer-1', $at, 1)->status);
    }

    public function test_claim_rejects_stale_version_and_preserves_external_rollback(): void
    {
        $event = $this->event('33333333-3333-4333-8333-333333333333', 2, '2026-08-10T10:02:00.000000Z');
        self::assertSame(PublicationReviewIngestionResult::Applied, $this->queue->ingest($event));
        self::assertSame(PublicationReviewClaimStatus::VersionConflict, $this->queue->claim('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', $event->eventId->value, 'reviewer-2', new DateTimeImmutable('2026-08-10T11:00:00+00:00'), 2)->status);

        $this->connection->beginTransaction();
        self::assertSame(PublicationReviewClaimStatus::Applied, $this->queue->claim('cccccccc-cccc-4ccc-8ccc-cccccccccccc', $event->eventId->value, 'reviewer-2', new DateTimeImmutable('2026-08-10T11:01:00+00:00'), 1)->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(1, $this->queue->read(null, 10)->items[0]->version);
    }

    public function test_rollback_is_owner_scoped_and_migration_is_reversible(): void
    {
        $root = dirname(__DIR__, 3);
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/Migrations/096_publication_review_queue.down.sql'));
        self::assertFalse((bool) $this->connection->query("SELECT to_regclass('publication_review.queue_items') IS NOT NULL")->fetchColumn());

        $this->connection->exec((string) file_get_contents($root.'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/Migrations/096_publication_review_queue.sql'));
        self::assertTrue((bool) $this->connection->query("SELECT to_regclass('publication_review.queue_items') IS NOT NULL")->fetchColumn());
    }

    public function test_review_commands_synchronize_queue_and_preserve_replay_guarantees(): void
    {
        $event = $this->event('44444444-4444-4444-8444-444444444444', 7, '2026-08-10T10:03:00.000000Z');
        self::assertSame(PublicationReviewIngestionResult::Applied, $this->queue->ingest($event));
        self::assertSame(PublicationReviewClaimStatus::Applied, $this->queue->claim('dddddddd-dddd-4ddd-8ddd-dddddddddddd', $event->eventId->value, 'reviewer-4', new DateTimeImmutable('2026-08-10T11:00:00+00:00'), 1)->status);

        $beginId = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
        $at = new DateTimeImmutable('2026-08-10T11:01:00+00:00');
        $applied = fn (): ListingPublicationCommandResultV1 => new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::Applied);
        $begin = $this->queue->execute('begin_review', $event->eventId->value, $beginId, 2, 'reviewer-4', $at, false, $applied);
        self::assertSame(PublicationReviewCommandStatus::Applied, $begin->status);
        self::assertSame(3, $begin->queueVersion);
        self::assertSame(PublicationReviewCommandStatus::AlreadyApplied, $this->queue->execute('begin_review', $event->eventId->value, $beginId, 2, 'reviewer-4', $at, false, $applied)->status);
        self::assertSame(PublicationReviewCommandStatus::DivergentCommand, $this->queue->execute('begin_review', $event->eventId->value, $beginId, 2, 'another-reviewer', $at, false, $applied)->status);
        self::assertSame(PublicationReviewCommandStatus::VersionConflict, $this->queue->execute('approve_and_publish', $event->eventId->value, 'ffffffff-ffff-4fff-8fff-ffffffffffff', 2, 'reviewer-4', $at, true, $applied)->status);

        $approve = $this->queue->execute('approve_and_publish', $event->eventId->value, 'ffffffff-ffff-4fff-8fff-ffffffffffff', 3, 'reviewer-4', $at, true, $applied);
        self::assertSame(PublicationReviewCommandStatus::Applied, $approve->status);
        self::assertSame(4, $approve->queueVersion);
        $state = $this->connection->query("SELECT state FROM publication_review.queue_items WHERE queue_item_id='".$event->eventId->value."'")->fetchColumn();
        self::assertSame(PublicationReviewQueueItemState::Completed->value, $state);
    }

    public function test_review_command_preserves_external_rollback(): void
    {
        $event = $this->event('55555555-5555-4555-8555-555555555555', 3, '2026-08-10T10:04:00.000000Z');
        $this->queue->ingest($event);
        $this->queue->claim('12121212-1212-4212-8212-121212121212', $event->eventId->value, 'reviewer-5', new DateTimeImmutable('2026-08-10T11:00:00+00:00'), 1);
        $this->connection->beginTransaction();
        $result = $this->queue->execute('begin_review', $event->eventId->value, '13131313-1313-4313-8313-131313131313', 2, 'reviewer-5', new DateTimeImmutable('2026-08-10T11:01:00+00:00'), false, fn () => new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::Applied));
        self::assertSame(PublicationReviewCommandStatus::Applied, $result->status);
        $this->connection->rollBack();

        self::assertSame(2, (int) $this->connection->query("SELECT version FROM publication_review.queue_items WHERE queue_item_id='".$event->eventId->value."'")->fetchColumn());
        self::assertFalse($this->queue->hasRecorded('13131313-1313-4313-8313-131313131313'));
    }

    public function test_projection_activation_is_idempotent_and_synchronizes_the_terminal_queue(): void
    {
        $event = $this->event('66666666-6666-4666-8666-666666666666', 7, '2026-08-10T10:05:00.000000Z');
        $this->queue->ingest($event);
        $this->queue->claim('14141414-1414-4414-8414-141414141414', $event->eventId->value, 'reviewer-6', new DateTimeImmutable('2026-08-10T11:00:00+00:00'), 1);
        $gateway = fn (): ListingPublicationCommandResultV1 => new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::Applied);
        $this->queue->execute('begin_review', $event->eventId->value, '15151515-1515-4515-8515-151515151515', 2, 'reviewer-6', new DateTimeImmutable('2026-08-10T11:01:00+00:00'), false, $gateway);
        $this->queue->execute('approve_and_publish', $event->eventId->value, '16161616-1616-4616-8616-161616161616', 3, 'reviewer-6', new DateTimeImmutable('2026-08-10T11:02:00+00:00'), true, $gateway);

        $command = '17171717-1717-4717-8717-171717171717';
        $at = new DateTimeImmutable('2026-08-10T11:03:00+00:00');
        $calls = 0;
        $activation = function () use (&$calls): ProjectionActivationStatus {
            $calls++;

            return ProjectionActivationStatus::Applied;
        };
        $projected = $this->queue->activate($event->payload->listingId->value, 9, $command, $at, $activation);
        self::assertSame(ProjectPublishedListingStatus::Applied, $projected->status);
        self::assertSame(5, $projected->queueVersion);
        self::assertSame(1, $calls);
        self::assertSame(ProjectPublishedListingStatus::AlreadyApplied, $this->queue->activate($event->payload->listingId->value, 9, $command, $at, $activation)->status);
        self::assertSame(1, $calls, 'Replay must not execute Projection again.');
        self::assertSame(ProjectPublishedListingStatus::Conflict, $this->queue->activate($event->payload->listingId->value, 9, $command, new DateTimeImmutable('2026-08-10T11:04:00+00:00'), $activation)->status);
        self::assertSame(1, $calls);
        self::assertSame('completed', $this->connection->query("SELECT state FROM publication_review.queue_items WHERE queue_item_id='".$event->eventId->value."'")->fetchColumn());
    }

    public function test_projection_activation_reports_not_ready_without_invoking_projection(): void
    {
        $calls = 0;
        $result = $this->queue->activate(
            '77777777-7777-4777-8777-777777777777',
            3,
            '18181818-1818-4818-8818-181818181818',
            new DateTimeImmutable('2026-08-10T11:05:00+00:00'),
            function () use (&$calls): ProjectionActivationStatus {
                $calls++;

                return ProjectionActivationStatus::Applied;
            },
        );

        self::assertSame(ProjectPublishedListingStatus::NotReady, $result->status);
        self::assertSame(0, $calls);
    }

    private function event(string $listingId, int $version, string $at): ListingPublicationEvent
    {
        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString($listingId),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            $version,
            new ListingPublicationEventMetadata(ListingPublicationEventInstant::fromCanonicalUtc($at), ListingPublicationEventInstant::fromCanonicalUtc($at)),
        )[0];
    }
}
