<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleOutboxOwnerSchemaArchitectureTest extends TestCase
{
    public function test_historical_migration_005_remains_without_reservation_owner(): void
    {
        $historical = (string) file_get_contents($this->migrationRoot().'/005_public_projection_outbox.sql');

        self::assertStringNotContainsString('ReservationLifecycle', $historical);
        self::assertStringNotContainsString('reservation_lifecycle', $historical);
        self::assertSame(1, substr_count($historical, "ARRAY['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo']"));
    }

    public function test_migration_021_is_additive_owner_only_and_reversible(): void
    {
        $up = (string) file_get_contents($this->migrationRoot().'/021_reservation_lifecycle_outbox_owner.sql');
        $down = (string) file_get_contents($this->migrationRoot().'/021_reservation_lifecycle_outbox_owner.down.sql');

        self::assertSame(4, substr_count($up, 'CREATE TABLE IF NOT EXISTS reservation_lifecycle.'));
        self::assertSame(2, substr_count($up, 'CREATE INDEX IF NOT EXISTS'));
        self::assertSame(4, substr_count($down, 'DROP TABLE IF EXISTS reservation_lifecycle.'));
        foreach (['listing_lifecycle.', 'real_estate_catalog.', 'media.', 'search_discovery.', 'content_seo.'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $up);
            self::assertStringNotContainsString($forbidden, $down);
        }
    }

    public function test_writer_and_reader_use_the_same_owner_resolver(): void
    {
        $writer = (string) file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxWriter.php');
        $reader = (string) file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxReader.php');

        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule)', $writer);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::all()', $reader);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::moduleFor($schema)', $reader);
        self::assertStringContainsString('m.source_module=:owner_module', $reader);
    }

    public function test_later_compatibility_uses_the_certified_owner_without_modifying_its_migration(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');

        self::assertStringContainsString('ReservationLifecycleDeliveryPayload', $catalog);
        self::assertStringContainsString('ReservationLifecycleDeliveryPayload::restore', $mapper);
        self::assertSame(4, substr_count((string) file_get_contents($this->migrationRoot().'/021_reservation_lifecycle_outbox_owner.sql'), 'CREATE TABLE IF NOT EXISTS reservation_lifecycle.'));
    }

    private function migrationRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations';
    }
}
