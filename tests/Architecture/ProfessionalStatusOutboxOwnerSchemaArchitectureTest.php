<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusOutboxOwnerSchemaArchitectureTest extends TestCase
{
    public function test_historical_migrations_remain_without_professionals_owner(): void
    {
        foreach (['005_public_projection_outbox.sql', '021_reservation_lifecycle_outbox_owner.sql', '026_contacts_leads_outbox_owner.sql'] as $migration) {
            $contents = (string) file_get_contents($this->migrationRoot().'/'.$migration);
            self::assertStringNotContainsString('Professionals', $contents);
            self::assertStringNotContainsString('professionals.', $contents);
        }
    }

    public function test_migration_030_is_additive_owner_only_and_reversible(): void
    {
        $up = (string) file_get_contents($this->migrationRoot().'/030_professionals_outbox_owner.sql');
        $down = (string) file_get_contents($this->migrationRoot().'/030_professionals_outbox_owner.down.sql');
        self::assertSame(4, substr_count($up, 'CREATE TABLE IF NOT EXISTS professionals.'));
        self::assertSame(2, substr_count($up, 'CREATE INDEX IF NOT EXISTS'));
        self::assertSame(4, substr_count($down, 'DROP TABLE IF EXISTS professionals.'));
        foreach (['listing_lifecycle.', 'real_estate_catalog.', 'media.', 'search_discovery.', 'content_seo.', 'reservation_lifecycle.', 'contacts_leads.'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $up);
            self::assertStringNotContainsString($forbidden, $down);
        }
    }

    public function test_writer_and_reader_keep_single_owner_resolver(): void
    {
        $root = dirname(__DIR__, 2);
        $writer = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxWriter.php');
        $reader = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxReader.php');
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule)', $writer);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::all()', $reader);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::moduleFor($schema)', $reader);
        self::assertStringContainsString('m.source_module=:owner_module', $reader);
    }

    private function migrationRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations';
    }
}
