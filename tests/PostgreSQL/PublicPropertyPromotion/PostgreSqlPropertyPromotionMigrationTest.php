<?php

namespace Tests\PostgreSQL\PublicPropertyPromotion;

use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyPromotionMigrationTest extends TestCase
{
    public function test_migration_is_additive_and_rollback_preserves_historical_tables(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        $root = dirname(__DIR__, 3);
        $down = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.down.sql');
        $up = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.sql');

        $connection->exec($down);
        self::assertSame('', (string) $connection->query("SELECT COALESCE(to_regclass('real_estate_catalog.property_promotion_commands')::text,'')")->fetchColumn());
        self::assertSame('real_estate_catalog.properties', $connection->query("SELECT to_regclass('real_estate_catalog.properties')::text")->fetchColumn());
        self::assertSame('real_estate_catalog_authoring.property_authoring', $connection->query("SELECT to_regclass('real_estate_catalog_authoring.property_authoring')::text")->fetchColumn());
        $connection->exec($up);
        self::assertSame('real_estate_catalog.property_promotion_commands', $connection->query("SELECT to_regclass('real_estate_catalog.property_promotion_commands')::text")->fetchColumn());
    }
}
