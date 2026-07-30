<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
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

final class PostgreSqlReservationLifecycleOutboxOwnerSchemaTest extends TestCase
{
    private PDO $connection;

    private PublicProjectionOutboxConsumerId $consumer;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->consumer = PublicProjectionOutboxConsumerId::fromString('reservation-owner-schema-test');
    }

    public function test_writer_targets_only_reservation_lifecycle_and_reader_restores_exact_message(): void
    {
        $message = $this->message('ReservationLifecycle', 'reservation:1');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $this->consumer);

        self::assertSame(1, $this->countRows('reservation_lifecycle', 'public_projection_outbox_messages'));
        foreach (['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo'] as $schema) {
            self::assertSame(0, $this->countRows($schema, 'public_projection_outbox_messages'), $schema);
        }

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($this->consumer, 10);
        self::assertCount(1, $records);
        self::assertEquals($message, $records[0]->message);
        self::assertSame('ReservationLifecycle', $records[0]->message->sourceModule->value);
        self::assertSame($message->payload->fields(), $records[0]->message->payload->fields());
        self::assertSame($message->payload->checksum(), $records[0]->message->payload->checksum());
    }

    public function test_wrong_owner_copy_with_identical_identity_is_never_read(): void
    {
        $message = $this->message('ReservationLifecycle', 'reservation:2');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $this->consumer);
        $this->connection->exec('INSERT INTO listing_lifecycle.public_projection_outbox_messages SELECT * FROM reservation_lifecycle.public_projection_outbox_messages');
        $this->connection->exec('INSERT INTO listing_lifecycle.public_projection_outbox_deliveries SELECT * FROM reservation_lifecycle.public_projection_outbox_deliveries');

        self::assertSame(1, $this->countRows('reservation_lifecycle', 'public_projection_outbox_messages'));
        self::assertSame(1, $this->countRows('listing_lifecycle', 'public_projection_outbox_messages'));
        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($this->consumer, 10);
        self::assertCount(1, $records);
        self::assertSame('ReservationLifecycle', $records[0]->message->sourceModule->value);
    }

    public function test_six_owner_reads_keep_the_existing_deterministic_order(): void
    {
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        $writer = new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper);
        $writer->append($this->message('ReservationLifecycle', 'reservation:3'), $this->consumer);
        $writer->append($this->message('ListingLifecycle', 'listing:3'), $this->consumer);

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($this->consumer, 10);
        self::assertSame(['ListingLifecycle', 'ReservationLifecycle'], array_map(static fn ($record): string => $record->message->sourceModule->value, $records));
    }

    public function test_migration_matches_historical_structure_and_is_reversible(): void
    {
        foreach (['public_projection_outbox_messages', 'public_projection_outbox_deliveries', 'public_projection_outbox_cursors', 'public_projection_outbox_replays'] as $table) {
            self::assertSame($this->columns('listing_lifecycle', $table), $this->columns('reservation_lifecycle', $table), $table);
            self::assertSame($this->constraintDefinitions('listing_lifecycle', $table), $this->constraintDefinitions('reservation_lifecycle', $table), $table);
        }
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM pg_indexes WHERE schemaname='reservation_lifecycle' AND indexname IN ('public_projection_outbox_claim_idx','public_projection_outbox_order_idx')")->fetchColumn());

        $root = dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'021_reservation_lifecycle_outbox_owner.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('reservation_lifecycle.public_projection_outbox_messages')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'021_reservation_lifecycle_outbox_owner.sql'));
        self::assertSame('reservation_lifecycle.public_projection_outbox_messages', $this->connection->query("SELECT to_regclass('reservation_lifecycle.public_projection_outbox_messages')")->fetchColumn());
    }

    private function message(string $module, string $aggregateId): PublicProjectionDeliveryMessage
    {
        $source = PublicProjectionDeliverySourceModule::fromString($module);
        $aggregateType = PublicProjectionDeliveryAggregateType::fromString('Listing');
        $aggregateIdentity = PublicProjectionDeliveryAggregateId::fromString($aggregateId);
        $eventType = PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested');
        $payloadVersion = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $eventIndex = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregateType, $aggregateIdentity, 1, $eventIndex, $eventType, $payloadVersion);

        return new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $eventType,
            $payloadVersion,
            $source,
            $aggregateType,
            $aggregateIdentity,
            new PublicProjectionDeliveryOrder(1, $eventIndex),
            new DateTimeImmutable('2026-07-21T10:00:00+00:00'),
            new DateTimeImmutable('2026-07-21T10:00:01+00:00'),
            new PublicProjectionDeliveryListingPayload($aggregateId),
        );
    }

    private function countRows(string $schema, string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$schema}.{$table}")->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    private function columns(string $schema, string $table): array
    {
        $statement = $this->connection->prepare('SELECT column_name,data_type,character_maximum_length,numeric_precision,datetime_precision,is_nullable,column_default IS NOT NULL AS has_default FROM information_schema.columns WHERE table_schema=:schema AND table_name=:table ORDER BY ordinal_position');
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<string> */
    private function constraintDefinitions(string $schema, string $table): array
    {
        $statement = $this->connection->prepare('SELECT pg_get_constraintdef(c.oid) FROM pg_constraint c JOIN pg_class t ON t.oid=c.conrelid JOIN pg_namespace n ON n.oid=t.relnamespace WHERE n.nspname=:schema AND t.relname=:table ORDER BY pg_get_constraintdef(c.oid)');
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return array_map(
            static fn (string $definition): string => str_replace(
                ['listing_lifecycle.', 'reservation_lifecycle.'],
                '<owner>.',
                $definition,
            ),
            $statement->fetchAll(PDO::FETCH_COLUMN),
        );
    }
}
