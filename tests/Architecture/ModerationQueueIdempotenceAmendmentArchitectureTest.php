<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationQueueIdempotenceAmendmentArchitectureTest extends TestCase
{
    #[Test]
    public function migration_064_is_additive_owner_local_and_063_is_untouched(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents.sql',
        );
        $rollback = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents.down.sql',
        );
        $baseline = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.sql',
        );

        self::assertStringContainsString('moderation_reports.queue_claim_intents', $migration);
        self::assertStringContainsString('PRIMARY KEY (queue_item_id, intent_id)', $migration);
        self::assertStringContainsString('DROP TABLE IF EXISTS moderation_reports.queue_claim_intents', $rollback);
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($migration));
        self::assertStringNotContainsString('CASCADE', strtoupper($migration));
        self::assertStringNotContainsString('TRIGGER', strtoupper($migration));
        self::assertStringNotContainsString('queue_claim_intents', $baseline);
    }

    #[Test]
    public function extension_is_limited_to_queue_persistence_and_runtime_boundaries(): void
    {
        $root = dirname(__DIR__, 2);
        $store = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/PostgreSqlModerationQueueStore.php',
        );
        $runtime = (string) file_get_contents(
            $root.'/app/Application/ModerationRuntime/DeterministicModerationQueueRuntimeV1.php',
        );
        foreach (['intentId', 'intentChecksum'] as $parameter) {
            self::assertStringContainsString($parameter, $store);
            self::assertStringContainsString($parameter, $runtime);
        }
        foreach (['IdentityAccess', 'ListingLifecycle', 'Media\\', 'Professionals\\', 'AdministrationAudit'] as $external) {
            self::assertStringNotContainsString($external, $store);
            self::assertStringNotContainsString($external, $runtime);
        }
    }
}
