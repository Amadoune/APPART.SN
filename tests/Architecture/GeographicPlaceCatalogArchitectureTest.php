<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class GeographicPlaceCatalogArchitectureTest extends TestCase
{
    public function test_catalog_boundary_is_domain_policy_plus_read_only_infrastructure_adapter(): void
    {
        $root = dirname(__DIR__, 2);
        $policy = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Domain/Policy/GeographicPlaceAddressabilityPolicy.php');
        $adapter = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Infrastructure/Geography/GeographyBackedGeographicPlaceCatalog.php');
        $provider = (string) file_get_contents($root.'/app/Providers/GeographicPlaceCatalogServiceProvider.php');

        self::assertStringContainsString('final readonly class GeographicPlaceAddressabilityPolicy', $policy);
        self::assertStringContainsString('implements GeographicPlaceCatalog', $adapter);
        self::assertStringContainsString('PlaceRegistry $places', $adapter);
        self::assertStringContainsString('->find(', $adapter);
        self::assertStringNotContainsString('->add(', $adapter);
        self::assertStringNotContainsString('->save(', $adapter);
        foreach (['PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ', 'Projection', 'Search', 'Http', 'GeographySelection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $policy.$adapter);
        }
        self::assertStringContainsString('GeographicPlaceCatalog::class', $provider);
    }

    public function test_geography_has_no_reverse_dependency_and_no_catalog_migration_exists(): void
    {
        $root = dirname(__DIR__, 2);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src/Modules/Geography'));
        foreach ($files as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                self::assertStringNotContainsString('Modules\\RealEstateCatalog', (string) file_get_contents($file->getPathname()));
            }
        }
        self::assertSame([], glob($root.'/src/Modules/RealEstateCatalog/**/Migrations/*geographic_place_catalog*') ?: []);
    }
}
