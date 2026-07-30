<?php

namespace Tests\PostgreSQL\PropertyLifecycleEventRouting;

use App\Application\PropertyLifecycleEventRouting\DurablePropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingStatus;
use App\Infrastructure\PropertyLifecycleEventRouting\PostgreSql\PostgreSqlPropertyLifecycleEventInbox;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyLifecycleEventInboxTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_routed_means_the_complete_canonical_event_is_durable_and_pending(): void
    {
        $event = $this->event();
        $result = $this->router()->route($event);
        $row = $this->connection->query('SELECT * FROM real_estate_catalog.property_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);

        self::assertSame(PropertyLifecycleEventRoutingStatus::Routed, $result->status);
        self::assertIsArray($row);
        self::assertStringStartsWith('plei:', $row['inbox_id']);
        self::assertNotSame($event->eventId->value, $row['inbox_id']);
        self::assertSame($event->eventId->value, $row['event_id']);
        self::assertSame((new PropertyLifecycleEventSerializer)->serialize($event), $row['canonical_event']);
        self::assertSame(hash('sha256', $row['canonical_event']), $row['event_checksum']);
        self::assertSame('pending', $row['status']);
        self::assertSame(0, (int) $row['delivery_attempts']);
    }

    public function test_identical_transfer_is_idempotent_and_remains_routed(): void
    {
        $event = $this->event();
        self::assertSame(PropertyLifecycleEventRoutingStatus::Routed, $this->router()->route($event)->status);
        self::assertSame(PropertyLifecycleEventRoutingStatus::Routed, $this->router()->route($event)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM real_estate_catalog.property_lifecycle_event_inbox')->fetchColumn());
    }

    public function test_same_event_identity_with_divergent_canonical_content_is_rejected(): void
    {
        $first = $this->event();
        $second = $this->event('2026-07-21T10:00:02.000000Z');
        self::assertSame($first->eventId->value, $second->eventId->value);
        self::assertSame(PropertyLifecycleEventRoutingStatus::Routed, $this->router()->route($first)->status);
        self::assertSame(PropertyLifecycleEventRoutingStatus::Rejected, $this->router()->route($second)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM real_estate_catalog.property_lifecycle_event_inbox')->fetchColumn());
    }

    public function test_pending_recovery_uses_the_deterministic_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $plan = implode("\n", $this->connection->query("EXPLAIN (FORMAT TEXT) SELECT inbox_id FROM real_estate_catalog.property_lifecycle_event_inbox WHERE status='pending' ORDER BY inbox_id LIMIT 1")->fetchAll(PDO::FETCH_COLUMN));
        self::assertStringContainsString('property_lifecycle_event_inbox_pending_lookup', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/PropertyLifecycleEventRouting/PostgreSql/Migrations/';
        $down = (string) file_get_contents($root.'018_property_lifecycle_event_inbox.down.sql');
        $up = (string) file_get_contents($root.'018_property_lifecycle_event_inbox.sql');
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('real_estate_catalog.property_lifecycle_event_inbox')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('real_estate_catalog.property_lifecycle_event_inbox', $this->connection->query("SELECT to_regclass('real_estate_catalog.property_lifecycle_event_inbox')")->fetchColumn());
    }

    private function router(): DurablePropertyLifecycleEventRouter
    {
        return new DurablePropertyLifecycleEventRouter(new PostgreSqlPropertyLifecycleEventInbox($this->connection, new PropertyLifecycleEventSerializer));
    }

    private function event(string $recordedAt = '2026-07-21T10:00:01.000000Z'): PropertyLifecycleEvent
    {
        return (new PropertyLifecycleEventCatalog)->eventsFor(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            new PropertyLifecycleEventMetadata(
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
                PropertyLifecycleEventInstant::fromCanonicalUtc($recordedAt),
            ),
        )[0];
    }
}
