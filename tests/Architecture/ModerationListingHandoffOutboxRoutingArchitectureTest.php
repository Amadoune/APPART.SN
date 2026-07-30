<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationListingHandoffOutboxRoutingArchitectureTest extends TestCase
{
    #[Test]
    public function extension_is_owner_local_filtered_and_requires_no_migration(): void
    {
        $root = dirname(__DIR__, 2);
        $router = (string) file_get_contents(
            $root.'/app/Application/ModerationEventRouting/DeterministicModerationEventRouter.php',
        );
        $outbox = (string) file_get_contents(
            $root.'/app/Infrastructure/ModerationEventOutbox/PostgreSql/PostgreSqlModerationOutbox.php',
        );
        $port = (string) file_get_contents(
            $root.'/app/Application/ModerationEventOutbox/Contract/ModerationOutboxReaderV1.php',
        );

        self::assertSame(1, substr_count($router, 'ModerationRoutingDestination::ListingHandoff'));
        self::assertStringContainsString('claimNextForDestination', $port);
        self::assertStringContainsString('AND d.destination=:destination', $outbox);
        self::assertStringContainsString('FOR UPDATE OF d SKIP LOCKED', $outbox);
        self::assertFileDoesNotExist(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff.sql',
        );
        foreach (['ListingModeration', 'ModeratorAuthorization', 'IdentityAccess'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $router.$outbox.$port);
        }
    }
}
