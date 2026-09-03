<?php

namespace Tests\PostgreSQL\MediaIngestionMigration;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaIngestionMigrationRollbackTest extends TestCase
{
    private const TABLES_058 = [
        'asset_intents',
        'assets',
        'processing',
        'processing_intents',
        'quota_intents',
        'quotas',
        'upload_intents',
        'uploads',
    ];

    #[Test]
    public function migration_058_rolls_back_and_reapplies_without_touching_objects_outside_its_schema(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);

        $connection->beginTransaction();
        try {
            $this->removeMigrations060And058($connection);
            $objectsOutsideOwnerScope = $this->relationsOutsideMediaIngestion($connection);

            $connection->exec($this->sql('058_media_ingestion.sql'));
            self::assertSame(self::TABLES_058, $this->tablesInMediaIngestion($connection));
            $ownerScopedObjects = $this->ownerScopedObjects($connection);

            $connection->exec($this->sql('058_media_ingestion.down.sql'));
            self::assertFalse($this->schemaExists($connection, 'media_ingestion'));
            self::assertSame($objectsOutsideOwnerScope, $this->relationsOutsideMediaIngestion($connection));

            $connection->exec($this->sql('058_media_ingestion.sql'));
            self::assertSame(self::TABLES_058, $this->tablesInMediaIngestion($connection));
            self::assertSame($ownerScopedObjects, $this->ownerScopedObjects($connection));
            self::assertSame($objectsOutsideOwnerScope, $this->relationsOutsideMediaIngestion($connection));
        } finally {
            $connection->rollBack();
        }
    }

    #[Test]
    public function migrations_058_to_060_roll_back_in_reverse_order(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);

        $connection->beginTransaction();
        try {
            $connection->exec($this->sql('060_media_ingestion_event_outbox.down.sql'));
            $connection->exec($this->sql('059_media_attachment_intents.down.sql'));
            $connection->exec($this->sql('058_media_ingestion.down.sql'));

            $connection->exec($this->sql('058_media_ingestion.sql'));
            $connection->exec($this->sql('059_media_attachment_intents.sql'));
            $connection->exec($this->sql('060_media_ingestion_event_outbox.sql'));

            $expectedTables = [...self::TABLES_058, 'event_outbox_deliveries', 'event_outbox_messages'];
            sort($expectedTables);
            self::assertSame($expectedTables, $this->tablesInMediaIngestion($connection));
            self::assertTrue($this->tableExists($connection, 'media', 'media_attachment_intents'));

            $connection->exec($this->sql('060_media_ingestion_event_outbox.down.sql'));
            $connection->exec($this->sql('059_media_attachment_intents.down.sql'));
            $connection->exec($this->sql('058_media_ingestion.down.sql'));

            self::assertFalse($this->schemaExists($connection, 'media_ingestion'));
            self::assertFalse($this->tableExists($connection, 'media', 'media_attachment_intents'));
        } finally {
            $connection->rollBack();
        }
    }

    private function removeMigrations060And058(PDO $connection): void
    {
        $connection->exec($this->sql('060_media_ingestion_event_outbox.down.sql'));
        $connection->exec($this->sql('058_media_ingestion.down.sql'));
    }

    /** @return list<string> */
    private function tablesInMediaIngestion(PDO $connection): array
    {
        $statement = $connection->query(<<<'SQL'
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'media_ingestion'
              AND table_type = 'BASE TABLE'
            ORDER BY table_name
            SQL);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /** @return list<string> */
    private function relationsOutsideMediaIngestion(PDO $connection): array
    {
        $statement = $connection->query(<<<'SQL'
            SELECT n.nspname || '.' || c.relname || ':' || c.relkind::text
            FROM pg_catalog.pg_class c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname NOT IN ('media_ingestion', 'pg_catalog', 'information_schema')
              AND n.nspname NOT LIKE 'pg_toast%'
              AND n.nspname NOT LIKE 'pg_temp_%'
              AND n.nspname NOT LIKE 'pg_toast_temp_%'
            ORDER BY 1
            SQL);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /** @return list<string> */
    private function ownerScopedObjects(PDO $connection): array
    {
        $statement = $connection->query(<<<'SQL'
            SELECT 'relation:' || c.relkind::text || ':' || c.relname
            FROM pg_catalog.pg_class c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname = 'media_ingestion'
            UNION ALL
            SELECT 'constraint:' || c.conname || ':' || pg_catalog.pg_get_constraintdef(c.oid)
            FROM pg_catalog.pg_constraint c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.connamespace
            WHERE n.nspname = 'media_ingestion'
            ORDER BY 1
            SQL);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function schemaExists(PDO $connection, string $schema): bool
    {
        $statement = $connection->prepare('SELECT EXISTS (SELECT 1 FROM pg_catalog.pg_namespace WHERE nspname = :schema)');
        $statement->execute(['schema' => $schema]);

        return (bool) $statement->fetchColumn();
    }

    private function tableExists(PDO $connection, string $schema, string $table): bool
    {
        $statement = $connection->prepare(<<<'SQL'
            SELECT EXISTS (
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = :schema
                  AND table_name = :table
                  AND table_type = 'BASE TABLE'
            )
            SQL);
        $statement->execute(['schema' => $schema, 'table' => $table]);

        return (bool) $statement->fetchColumn();
    }

    private function sql(string $migration): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/'.$migration,
        );
    }
}
