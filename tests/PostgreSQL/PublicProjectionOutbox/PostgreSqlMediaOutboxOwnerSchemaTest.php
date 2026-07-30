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

final class PostgreSqlMediaOutboxOwnerSchemaTest extends TestCase
{
    private PDO $connection;

    private PublicProjectionOutboxConsumerId $consumer;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->consumer = PublicProjectionOutboxConsumerId::fromString('media-owner-test');
    }

    public function test_generic_writer_and_reader_are_strictly_isolated_to_media(): void
    {
        $message = $this->message('media:item:owner:1');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;

        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $this->consumer);

        self::assertSame(1, $this->countRows('media'));
        foreach (['listing_lifecycle', 'real_estate_catalog', 'search_discovery', 'content_seo', 'reservation_lifecycle', 'contacts_leads', 'professionals'] as $schema) {
            self::assertSame(0, $this->countRows($schema), $schema);
        }

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($this->consumer, 10);

        self::assertCount(1, $records);
        self::assertEquals($message, $records[0]->message);
        self::assertSame('Media', $records[0]->message->sourceModule->value);
    }

    public function test_wrong_owner_copy_is_never_read_as_media(): void
    {
        $message = $this->message('media:item:owner:2');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $this->consumer);
        $this->connection->exec('INSERT INTO listing_lifecycle.public_projection_outbox_messages SELECT * FROM media.public_projection_outbox_messages');
        $this->connection->exec('INSERT INTO listing_lifecycle.public_projection_outbox_deliveries SELECT * FROM media.public_projection_outbox_deliveries');

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($this->consumer, 10);

        self::assertCount(1, $records);
        self::assertSame('Media', $records[0]->message->sourceModule->value);
    }

    public function test_historical_media_owner_matches_the_generic_owner_contract(): void
    {
        foreach (['public_projection_outbox_messages', 'public_projection_outbox_deliveries', 'public_projection_outbox_cursors', 'public_projection_outbox_replays'] as $table) {
            self::assertSame($this->columns('listing_lifecycle', $table), $this->columns('media', $table), $table);
            self::assertSame($this->constraints('listing_lifecycle', $table), $this->constraints('media', $table), $table);
        }

        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM pg_indexes WHERE schemaname='media' AND indexname IN ('public_projection_outbox_claim_idx','public_projection_outbox_order_idx')")->fetchColumn());
        self::assertSame('media.media_item_lifecycle_event_inbox', $this->connection->query("SELECT to_regclass('media.media_item_lifecycle_event_inbox')")->fetchColumn());
    }

    private function message(string $aggregateId): PublicProjectionDeliveryMessage
    {
        $source = PublicProjectionDeliverySourceModule::fromString('Media');
        $type = PublicProjectionDeliveryAggregateType::fromString('MediaItemLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($aggregateId);
        $event = PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested');
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $type, $id, 1, $index, $event, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $event, $version, $source, $type, $id, new PublicProjectionDeliveryOrder(1, $index), new DateTimeImmutable('2026-07-23T10:00:00+00:00'), new DateTimeImmutable('2026-07-23T10:00:01+00:00'), new PublicProjectionDeliveryListingPayload($aggregateId));
    }

    private function countRows(string $schema): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$schema}.public_projection_outbox_messages")->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    private function columns(string $schema, string $table): array
    {
        $statement = $this->connection->prepare('SELECT column_name,data_type,character_maximum_length,numeric_precision,datetime_precision,is_nullable,column_default IS NOT NULL AS has_default FROM information_schema.columns WHERE table_schema=:schema AND table_name=:table ORDER BY ordinal_position');
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<string> */
    private function constraints(string $schema, string $table): array
    {
        $statement = $this->connection->prepare('SELECT pg_get_constraintdef(c.oid) FROM pg_constraint c JOIN pg_class t ON t.oid=c.conrelid JOIN pg_namespace n ON n.oid=t.relnamespace WHERE n.nspname=:schema AND t.relname=:table ORDER BY pg_get_constraintdef(c.oid)');
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return array_map(static fn (string $definition): string => str_replace(['listing_lifecycle.', 'media.'], '<owner>.', $definition), $statement->fetchAll(PDO::FETCH_COLUMN));
    }
}
