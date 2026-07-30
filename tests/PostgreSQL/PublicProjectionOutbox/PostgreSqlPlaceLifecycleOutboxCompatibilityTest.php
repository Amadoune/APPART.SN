<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\Geography\DurablePlaceLifecycleEventRouterTest;

final class PostgreSqlPlaceLifecycleOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_generic_writer_mapper_and_reader_round_trip_place_payload_in_geography_only(): void
    {
        $message = $this->message();
        $consumer = PublicProjectionOutboxConsumerId::fromString('place-lifecycle-owner-test');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;

        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $consumer);

        self::assertSame(1, $this->countRows('geography'));
        foreach (['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo', 'reservation_lifecycle', 'contacts_leads', 'professionals', 'administration_audit'] as $schema) {
            self::assertSame(0, $this->countRows($schema), $schema);
        }

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))
            ->findClaimable($consumer, 10);
        self::assertCount(1, $records);
        self::assertEquals($message, $records[0]->message);
        self::assertSame($message->payload->fields(), $records[0]->message->payload->fields());
        self::assertSame($message->payload->checksum(), $records[0]->message->payload->checksum());
    }

    public function test_migration_is_structurally_identical_and_rollback_preserves_geography_foundations(): void
    {
        foreach (['public_projection_outbox_messages', 'public_projection_outbox_deliveries', 'public_projection_outbox_cursors', 'public_projection_outbox_replays'] as $table) {
            self::assertSame($this->columns('listing_lifecycle', $table), $this->columns('geography', $table), $table);
            self::assertSame($this->constraints('listing_lifecycle', $table), $this->constraints('geography', $table), $table);
        }

        $root = dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'040_geography_outbox_owner.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('geography.public_projection_outbox_messages')")->fetchColumn());
        self::assertSame(
            'geography.place_lifecycle_event_inbox',
            $this->connection->query("SELECT to_regclass('geography.place_lifecycle_event_inbox')")->fetchColumn(),
        );
        self::assertSame(
            'geography.place_lifecycle_transitions',
            $this->connection->query("SELECT to_regclass('geography.place_lifecycle_transitions')")->fetchColumn(),
        );
        $this->connection->exec((string) file_get_contents($root.'040_geography_outbox_owner.sql'));
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $payload = DurablePlaceLifecycleEventRouterTest::envelope()->payload;
        $event = $payload->event;
        $source = PublicProjectionDeliverySourceModule::fromString('Geography');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('PlaceLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->placeId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->type->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents(
            $source,
            $aggregate,
            $id,
            $event->payload->occurredVersion,
            $index,
            $type,
            $version,
        );

        return new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $type,
            $version,
            $source,
            $aggregate,
            $id,
            new PublicProjectionDeliveryOrder($event->payload->occurredVersion, $index),
            new DateTimeImmutable($event->occurredAt->canonical()),
            new DateTimeImmutable($event->occurredAt->canonical()),
            $payload,
        );
    }

    private function countRows(string $schema): int
    {
        return (int) $this->connection
            ->query("SELECT count(*) FROM {$schema}.public_projection_outbox_messages")
            ->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    private function columns(string $schema, string $table): array
    {
        $statement = $this->connection->prepare(
            'SELECT column_name,data_type,character_maximum_length,numeric_precision,datetime_precision,is_nullable,column_default IS NOT NULL AS has_default
             FROM information_schema.columns
             WHERE table_schema=:schema AND table_name=:table
             ORDER BY ordinal_position',
        );
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<string> */
    private function constraints(string $schema, string $table): array
    {
        $statement = $this->connection->prepare(
            'SELECT pg_get_constraintdef(c.oid)
             FROM pg_constraint c
             JOIN pg_class t ON t.oid=c.conrelid
             JOIN pg_namespace n ON n.oid=t.relnamespace
             WHERE n.nspname=:schema AND t.relname=:table
             ORDER BY pg_get_constraintdef(c.oid)',
        );
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return array_map(
            static fn (string $definition): string => str_replace(
                ['listing_lifecycle.', 'geography.'],
                '<owner>.',
                $definition,
            ),
            $statement->fetchAll(PDO::FETCH_COLUMN),
        );
    }
}
