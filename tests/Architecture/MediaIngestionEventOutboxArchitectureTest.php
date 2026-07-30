<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaIngestionEventOutboxArchitectureTest extends TestCase
{
    public function test_outbox_is_owner_scoped_additive_and_http_free(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/060_media_ingestion_event_outbox.sql');
        $store = (string) file_get_contents($root.'/app/Infrastructure/MediaIngestionEventOutbox/PostgreSql/PostgreSqlMediaIngestionOutbox.php');
        $transaction = (string) file_get_contents($root.'/app/Infrastructure/MediaIngestionEventOutbox/PostgreSql/PostgreSqlMediaIngestionAtomicDeliveryTransaction.php');
        $integration = (string) file_get_contents($root.'/app/Application/MediaIngestionEventIntegration/MediaIngestionAtomicDelivery.php');

        self::assertStringContainsString('media_ingestion.event_outbox_messages', $migration);
        self::assertStringContainsString('media_ingestion.event_outbox_deliveries', $migration);
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($migration));
        self::assertStringNotContainsString('CASCADE', strtoupper($migration));
        self::assertStringContainsString('ON CONFLICT DO NOTHING', $store);
        self::assertStringContainsString('FOR UPDATE OF d SKIP LOCKED', $store);
        self::assertStringContainsString('SAVEPOINT', $transaction);

        foreach ([$store, $transaction, $integration] as $source) {
            self::assertStringNotContainsString('Illuminate\\Http', $source);
            self::assertStringNotContainsString('RuntimeHealth', $source);
            self::assertStringNotContainsString('IdentityAccess', $source);
        }
    }
}
