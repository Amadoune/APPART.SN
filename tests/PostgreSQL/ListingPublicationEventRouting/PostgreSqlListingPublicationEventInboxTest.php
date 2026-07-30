<?php

namespace Tests\PostgreSQL\ListingPublicationEventRouting;

use App\Application\ListingPublicationEventRouting\DurableListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingStatus;
use App\Infrastructure\ListingPublicationEventRouting\PostgreSql\PostgreSqlListingPublicationEventInbox;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingPublicationEventInboxTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_routed_means_the_complete_canonical_event_is_durable(): void
    {
        $event = $this->event();
        $result = $this->router()->route($event);
        $row = $this->connection->query('SELECT * FROM listing_lifecycle.publication_event_inbox')->fetch(PDO::FETCH_ASSOC);

        self::assertSame(ListingPublicationEventRoutingStatus::Routed, $result->status);
        self::assertIsArray($row);
        self::assertSame($event->eventId->value, $row['event_id']);
        self::assertSame((new ListingPublicationEventSerializer)->serialize($event), $row['canonical_event']);
        self::assertSame(hash('sha256', $row['canonical_event']), $row['event_checksum']);
        self::assertSame('pending', $row['status']);
        self::assertSame(0, (int) $row['delivery_attempts']);
    }

    public function test_identical_transfer_is_idempotent_and_remains_routed(): void
    {
        $event = $this->event();
        self::assertSame(ListingPublicationEventRoutingStatus::Routed, $this->router()->route($event)->status);
        self::assertSame(ListingPublicationEventRoutingStatus::Routed, $this->router()->route($event)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM listing_lifecycle.publication_event_inbox')->fetchColumn());
    }

    public function test_same_business_identity_with_divergent_canonical_event_is_rejected(): void
    {
        $first = $this->event();
        $second = $this->event('2026-07-20T10:00:02.000000Z');
        self::assertSame($first->eventId->value, $second->eventId->value);
        self::assertSame(ListingPublicationEventRoutingStatus::Routed, $this->router()->route($first)->status);
        self::assertSame(ListingPublicationEventRoutingStatus::Rejected, $this->router()->route($second)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM listing_lifecycle.publication_event_inbox')->fetchColumn());
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/ListingPublicationEventRouting/PostgreSql/Migrations/';
        $down = file_get_contents($root.'016_listing_publication_event_inbox.down.sql');
        $up = file_get_contents($root.'016_listing_publication_event_inbox.sql');
        self::assertIsString($down);
        self::assertIsString($up);
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('listing_lifecycle.publication_event_inbox')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('listing_lifecycle.publication_event_inbox', $this->connection->query("SELECT to_regclass('listing_lifecycle.publication_event_inbox')")->fetchColumn());
    }

    public function test_pending_lookup_uses_the_dedicated_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $plan = implode("\n", $this->connection->query("EXPLAIN (FORMAT TEXT) SELECT inbox_id FROM listing_lifecycle.publication_event_inbox WHERE status='pending' ORDER BY inbox_id LIMIT 1")->fetchAll(PDO::FETCH_COLUMN));
        self::assertStringContainsString('publication_event_inbox_pending_lookup', $plan);
    }

    private function router(): DurableListingPublicationEventRouter
    {
        return new DurableListingPublicationEventRouter(new PostgreSqlListingPublicationEventInbox($this->connection, new ListingPublicationEventSerializer));
    }

    private function event(string $recordedAt = '2026-07-20T10:00:01.000000Z'): ListingPublicationEvent
    {
        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            2,
            new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
                ListingPublicationEventInstant::fromCanonicalUtc($recordedAt),
            ),
        )[0];
    }
}
