<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\AccountStatusDeliveryTestFactory;

final class PostgreSqlAccountStatusOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_generic_writer_mapper_and_reader_preserve_routed_delivery_v1(): void
    {
        $delivery = $this->delivery();
        $consumer = PublicProjectionOutboxConsumerId::fromString('account-status-routed-v1');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        $writer = new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper);

        self::assertSame(
            PublicProjectionOutboxWriteResult::Applied,
            $writer->appendRouted($delivery, $consumer),
        );
        self::assertSame(
            PublicProjectionOutboxWriteResult::AlreadyApplied,
            $writer->appendRouted($delivery, $consumer),
        );

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))
            ->findClaimable($consumer, 10);

        self::assertCount(1, $records);
        self::assertNotNull($records[0]->routedDelivery);
        self::assertEquals($delivery->deliveryMessage, $records[0]->message);
        self::assertSame(
            $delivery->destination->value,
            $records[0]->routedDelivery->destination->value,
        );
        self::assertSame(
            $delivery->routingProof->checksum,
            $records[0]->routedDelivery->routingProof->checksum,
        );
        self::assertTrue($records[0]->routedDelivery->hasValidRoutingProof());
        self::assertSame(1, $this->countRows('identity_access'));
        foreach (['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo', 'reservation_lifecycle', 'contacts_leads', 'professionals', 'administration_audit', 'geography'] as $schema) {
            self::assertSame(0, $this->countRows($schema), $schema);
        }
    }

    public function test_migration_043_is_reversible_without_touching_account_foundations(): void
    {
        $columns = $this->connection->query(
            "SELECT column_name FROM information_schema.columns
             WHERE table_schema='identity_access'
               AND table_name='public_projection_outbox_messages'
             ORDER BY ordinal_position",
        )->fetchAll(PDO::FETCH_COLUMN);

        self::assertContains('routing_destination', $columns);
        self::assertContains('routing_version', $columns);
        self::assertContains('routing_checksum', $columns);

        $root = dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'043_identity_access_outbox_owner.down.sql'));

        self::assertNull($this->connection->query(
            "SELECT to_regclass('identity_access.public_projection_outbox_messages')",
        )->fetchColumn());
        self::assertSame(
            'identity_access.account_status_lifecycle_transitions',
            $this->connection->query(
                "SELECT to_regclass('identity_access.account_status_lifecycle_transitions')",
            )->fetchColumn(),
        );
        self::assertSame(
            'identity_access.accounts',
            $this->connection->query("SELECT to_regclass('identity_access.accounts')")->fetchColumn(),
        );

        $this->connection->exec((string) file_get_contents($root.'043_identity_access_outbox_owner.sql'));
    }

    private function delivery(): PublicProjectionRoutedDeliveryMessageV1
    {
        return PublicProjectionRoutedDeliveryMessageV1::fromDecision(
            AccountStatusDeliveryTestFactory::genericMessage(),
            PublicProjectionDeliveryDestination::fromString(
                AccountStatusRoutingDestination::LifecycleFacts->value,
            ),
        );
    }

    private function countRows(string $schema): int
    {
        return (int) $this->connection
            ->query("SELECT count(*) FROM {$schema}.public_projection_outbox_messages")
            ->fetchColumn();
    }
}
