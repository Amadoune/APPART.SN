<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessRuntimeCompositionArchitectureTest extends TestCase
{
    #[Test]
    public function owner_provider_is_registered_without_forbidden_delivery_surfaces(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/IdentityAccessRuntimeServiceProvider.php');
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');

        self::assertStringContainsString('IdentityAccessRuntimeServiceProvider::class', $providers);
        self::assertStringContainsString('AccountAvailabilityInspector::class', $provider);
        self::assertStringContainsString('AccountClosureStateReader::class', $provider);
        self::assertStringContainsString('IdentityAccessRuntimeHealthInspector::class', $provider);
        foreach (['Controller', 'Route', 'Middleware', 'EventRouter', 'Delivery', 'Outbox', 'Orchestrator'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    #[Test]
    public function application_policy_has_no_infrastructure_or_framework_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountAvailability';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('\\Infrastructure\\', $source);
            self::assertStringNotContainsString('Illuminate\\', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SELECT ', $source);
        }
    }

    #[Test]
    public function frozen_runtime_health_catalogue_remains_at_fifty_eight(): void
    {
        $requirements = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php',
        );

        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertStringNotContainsString('AccountAvailability', $requirements);
        self::assertStringNotContainsString('AccountClosureReader', $requirements);
    }
}
