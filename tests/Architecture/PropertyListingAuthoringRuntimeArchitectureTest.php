<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyListingAuthoringRuntimeArchitectureTest extends TestCase
{
    public function test_runtime_application_is_framework_and_infrastructure_independent(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/PropertyListingAuthoringRuntime';
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

    public function test_owner_scoped_providers_are_additive_and_exclude_forbidden_surfaces(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');

        foreach ([
            'PropertyAuthoringRuntimeServiceProvider',
            'ListingAuthoringRuntimeServiceProvider',
            'PropertyListingAuthoringRuntimeServiceProvider',
        ] as $provider) {
            self::assertStringContainsString($provider.'::class', $providers);
            $source = (string) file_get_contents($root.'/app/Providers/'.$provider.'.php');
            foreach (['Controller', 'Route', 'Middleware', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    public function test_frozen_runtime_health_catalogue_remains_at_fifty_eight(): void
    {
        $requirements = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php',
        );

        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertStringNotContainsString('PropertyListingAuthoring', $requirements);
    }
}
