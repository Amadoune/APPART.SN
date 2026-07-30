<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyListingAuthoringHttpArchitectureTest extends TestCase
{
    public function test_http_application_has_no_infrastructure_pdo_or_laravel_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/PropertyListingAuthoringHttp';
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

    public function test_http_provider_and_controller_do_not_modify_frozen_surfaces(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PropertyListingAuthoringHttpServiceProvider.php');
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/PropertyListingAuthoringHttpController.php');

        self::assertStringContainsString('PropertyListingAuthoringHttpRuntime::class', $provider);
        self::assertStringContainsString('PropertyListingAuthoringRuntimeV1', (string) file_get_contents(
            $root.'/app/Application/PropertyListingAuthoringHttp/DeterministicPropertyListingAuthoringHttpRuntime.php',
        ));
        foreach (['Event', 'Delivery', 'Outbox', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_runtime_health_and_migrations_remain_unchanged(): void
    {
        $requirements = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php',
        );

        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertFileDoesNotExist(
            dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/058_property_listing_authoring_http.sql',
        );
    }
}
