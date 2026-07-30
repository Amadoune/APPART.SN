<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationListingHandoffIntegrationArchitectureTest extends TestCase
{
    #[Test]
    public function consumer_uses_only_certified_boundaries_and_owner_local_stores(): void
    {
        $root = dirname(__DIR__, 2);
        $consumer = (string) file_get_contents(
            $root.'/app/Application/ModerationListingHandoff/ModerationListingHandoffConsumer.php',
        );

        foreach ([
            'ModerationOutboxReaderV1',
            'ModeratorAuthorizationReaderV1',
            'ListingModerationReaderV1',
            'ListingModerationCommandGatewayV1',
            'ModerationCaseStore',
            'ModerationDecisionStore',
        ] as $required) {
            self::assertStringContainsString($required, $consumer);
        }
        foreach ([
            'ListingPublicationOrchestrator',
            'ListingPublicationWorkflowStore',
            'PostgreSql',
            'PDO',
            'Repository',
            'IdentityAccess\\Infrastructure',
            'ListingLifecycle\\Infrastructure',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $consumer);
        }
    }

    #[Test]
    public function terminal_integration_is_moderation_owner_local_and_atomic(): void
    {
        $root = dirname(__DIR__, 2);
        $integration = (string) file_get_contents(
            $root.'/app/Application/ModerationListingHandoff/ModerationListingHandoffTerminalIntegrator.php',
        );

        foreach ([
            'ModerationAtomicOperationV1',
            'ModerationOutboxAppenderV1',
            'ModerationListingHandoffResultStore',
            'ModerationEventTypeV1::TargetActionCompleted',
        ] as $required) {
            self::assertStringContainsString($required, $integration);
        }
        foreach ([
            'ListingModerationCommandGatewayV1',
            'ListingPublication',
            'ListingLifecycle\\Infrastructure',
            'AdministrationAudit',
            'PostgreSql',
            'PDO',
            'Repository',
            'SELECT ',
            'INSERT ',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $integration);
        }
    }

    #[Test]
    public function migration_is_additive_owner_local_and_has_complete_rollback(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff_results.sql',
        );
        $rollback = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff_results.down.sql',
        );

        self::assertStringContainsString('moderation_reports.listing_handoff_results', $migration);
        self::assertStringContainsString('DROP TABLE IF EXISTS moderation_reports.listing_handoff_results', $rollback);
        foreach (['FOREIGN KEY', 'CASCADE', 'TRIGGER'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($migration));
        }
    }

    #[Test]
    public function dedicated_provider_is_unique_and_runtime_health_is_untouched(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $provider = (string) file_get_contents(
            $root.'/app/Providers/ModerationListingHandoffServiceProvider.php',
        );

        self::assertSame(1, substr_count($providers, 'ModerationListingHandoffServiceProvider::class'));
        self::assertStringContainsString('singleton(ModerationListingHandoffConsumer::class)', $provider);
        self::assertStringContainsString('singleton(ModerationListingHandoffTerminalIntegrator::class)', $provider);
        self::assertStringNotContainsString('RuntimeHealth', $provider);
    }
}
