<?php

namespace Tests\PostgreSQL\AuthoringPersistence;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyAuthoringSourceCompletenessMigrationTest extends TestCase
{
    public function test_migration_is_additive_reversible_and_reapplicable_without_backfill(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $root = dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'099_property_authoring_source_completeness.sql');
        $down = (string) file_get_contents($root.'099_property_authoring_source_completeness.down.sql');

        $connection->beginTransaction();
        try {
            $connection->exec($down);
            self::assertFalse($this->columnExists($connection, 'property_reference'));
            $connection->exec($up);
            self::assertTrue($this->columnExists($connection, 'property_reference'));
            self::assertTrue($this->columnExists($connection, 'address_intent_id'));
            $connection->exec($up);
            self::assertTrue($this->columnExists($connection, 'geographic_place_id'));
        } finally {
            $connection->rollBack();
        }
    }

    private function columnExists(PDO $connection, string $column): bool
    {
        $statement = $connection->prepare("SELECT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='real_estate_catalog_authoring' AND table_name='property_authoring' AND column_name=:column)");
        $statement->execute(['column' => $column]);

        return (bool) $statement->fetchColumn();
    }
}
