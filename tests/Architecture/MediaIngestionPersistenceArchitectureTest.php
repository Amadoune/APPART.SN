<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaIngestionPersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_contracts_are_infrastructure_free(): void
    {
        $roots = [
            dirname(__DIR__, 2).'/src/Modules/Media/Application/IngestionPersistence',
            dirname(__DIR__, 2).'/src/Modules/Media/Application/Attachment',
        ];
        foreach ($roots as $root) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                self::assertStringNotContainsString('\\Infrastructure\\', $source);
                self::assertStringNotContainsString('PDO', $source);
                self::assertStringNotContainsString('Illuminate\\', $source);
                self::assertStringNotContainsString('Laravel', $source);
            }
        }
    }

    #[Test]
    public function migrations_are_additive_and_cross_domain_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/';
        $migration = (string) file_get_contents($root.'058_media_ingestion.sql').(string) file_get_contents($root.'059_media_attachment_intents.sql');

        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS media_ingestion', $migration);
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS media.media_attachment_intents', $migration);
        self::assertStringNotContainsString('ALTER TABLE', $migration);
        self::assertStringNotContainsString('REFERENCES', $migration);
        self::assertStringNotContainsString('CASCADE', $migration);
        self::assertStringNotContainsString('DROP TABLE', $migration);
    }

    #[Test]
    public function migration_058_rollback_explicitly_drops_only_its_eight_tables_before_its_schema(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/';
        $rollback = (string) file_get_contents($root.'058_media_ingestion.down.sql');

        self::assertStringNotContainsString('CASCADE', strtoupper($rollback));
        self::assertSame(8, substr_count(strtoupper($rollback), 'DROP TABLE IF EXISTS MEDIA_INGESTION.'));
        foreach (['uploads', 'assets', 'processing', 'quotas', 'upload_intents', 'asset_intents', 'processing_intents', 'quota_intents'] as $table) {
            self::assertStringContainsString('DROP TABLE IF EXISTS media_ingestion.'.$table.';', $rollback);
        }
        self::assertStringEndsWith("DROP SCHEMA IF EXISTS media_ingestion;\n", $rollback);
        self::assertStringNotContainsString('media.media_attachment_intents', $rollback);
        self::assertStringNotContainsString('event_outbox_', $rollback);
    }

    #[Test]
    public function each_owner_has_its_own_port_store_and_intent_table(): void
    {
        $application = dirname(__DIR__, 2).'/src/Modules/Media/Application/IngestionPersistence/Contract/';
        $infrastructure = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/';
        $migration = (string) file_get_contents($infrastructure.'Migrations/058_media_ingestion.sql');
        foreach (['Upload', 'Asset', 'Processing', 'Quota'] as $owner) {
            self::assertFileExists($application.'Media'.$owner.'Store.php');
            self::assertFileExists($infrastructure.'PostgreSqlMedia'.$owner.'Store.php');
            self::assertStringContainsString(strtolower($owner).'_intents', $migration);
        }
    }
}
