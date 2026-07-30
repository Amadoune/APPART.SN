<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationEventOutboxArchitectureTest extends TestCase
{
    #[Test]
    public function outbox_is_owner_local_additive_and_uses_the_atomic_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/067_moderation_event_outbox.sql',
        );
        $store = (string) file_get_contents(
            $root.'/app/Infrastructure/ModerationEventOutbox/PostgreSql/PostgreSqlModerationOutbox.php',
        );
        $atomic = (string) file_get_contents(
            $root.'/app/Application/ModerationAtomicOperation/ModerationAtomicMutation.php',
        );
        foreach (['FOREIGN KEY', 'CASCADE', 'TRIGGER'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($migration));
        }
        self::assertStringContainsString('moderation_reports.outbox_messages', $migration);
        self::assertStringContainsString('moderation_reports.outbox_deliveries', $migration);
        self::assertStringContainsString('FOR UPDATE OF d SKIP LOCKED', $store);
        self::assertStringContainsString('ModerationAtomicOperationV1', $atomic);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertStringContainsString('ModerationEventOutboxServiceProvider::class', $providers);
        foreach (['IdentityAccess', 'ListingLifecycle', 'MediaIngestion', 'Professional', 'AdministrationAudit'] as $external) {
            self::assertStringNotContainsString($external, $store);
        }
    }
}
