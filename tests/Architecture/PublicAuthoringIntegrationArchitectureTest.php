<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicAuthoringIntegrationArchitectureTest extends TestCase
{
    public function test_integration_application_depends_only_on_certified_operations(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/PublicAuthoringIntegration';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('\\Infrastructure\\', $source);
            self::assertStringNotContainsString('Illuminate\\', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('ListingPublicationOrchestrator', $source);
            self::assertStringNotContainsString('PropertyListingAuthoringRuntimeV1', $source);
        }
        $journey = (string) file_get_contents($directory.'/DeterministicPublicAuthoringJourney.php');
        self::assertStringContainsString('PropertyListingAuthoringOperations', $journey);
    }

    public function test_public_adapter_contains_no_persistence_or_frozen_delivery_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root.'/app/Http/Controllers/PublicAuthoringJourneyController.php',
            $root.'/app/Http/Requests/PublicAuthoringJourneyHttpRequest.php',
            $root.'/app/Providers/PublicAuthoringIntegrationServiceProvider.php',
        ];
        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            foreach (['PostgreSql', 'PDO', 'Outbox', 'Delivery', 'RuntimeHealth'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    public function test_ui_never_collects_account_identity_and_uses_security_headers(): void
    {
        $root = dirname(__DIR__, 2);
        $view = (string) file_get_contents($root.'/resources/views/authoring-workspace.blade.php');
        $script = (string) file_get_contents($root.'/resources/js/authoring.js');

        self::assertStringNotContainsString('name="accountId"', $view);
        self::assertStringContainsString('meta name="csrf-token"', $view);
        self::assertStringContainsString("'Idempotency-Key': crypto.randomUUID()", $script);
        self::assertStringContainsString("credentials: 'same-origin'", $script);
    }

    public function test_frozen_runtime_health_and_migrations_remain_unchanged(): void
    {
        $requirements = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php',
        );

        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertFileDoesNotExist(
            dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/058_public_authoring_integration.sql',
        );
    }
}
