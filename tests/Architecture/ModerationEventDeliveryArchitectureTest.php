<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationEventDeliveryArchitectureTest extends TestCase
{
    #[Test]
    public function catalog_is_closed_and_delivery_is_owner_local(): void
    {
        $root = dirname(__DIR__, 2);
        $type = (string) file_get_contents($root.'/src/Modules/ModerationReports/Application/ModerationEvent/ModerationEventTypeV1.php');
        self::assertSame(5, substr_count($type, 'case '));
        $migration = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/065_moderation_event_delivery.sql');
        foreach (['FOREIGN KEY', 'CASCADE', 'TRIGGER'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($migration));
        }
        $provider = (string) file_get_contents($root.'/app/Providers/ModerationEventDeliveryServiceProvider.php');
        self::assertStringNotContainsString('Outbox', $provider);
        self::assertStringNotContainsString('IdentityAccess', $provider);
        self::assertStringNotContainsString('ListingLifecycle', $provider);
    }
}
