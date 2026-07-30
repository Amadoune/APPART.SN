<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PropertyListingAuthoringPersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_persistence_contracts_are_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/AuthoringPersistence';
        $roots = [$root, dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/AuthoringPersistence'];
        foreach ($roots as $applicationRoot) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($applicationRoot));
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                self::assertStringNotContainsString('\\Infrastructure\\', $source);
                self::assertStringNotContainsString('PDO', $source);
                self::assertStringNotContainsString('Illuminate\\', $source);
            }
        }
    }

    #[Test]
    public function migration_056_is_additive_and_has_no_cross_domain_foreign_key(): void
    {
        $property = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/056_property_authoring.sql');
        $listing = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/057_listing_authoring.sql');
        $migration = $property.$listing;

        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS real_estate_catalog_authoring', $migration);
        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS listing_authoring', $migration);
        self::assertStringNotContainsString('ALTER TABLE', $migration);
        self::assertStringNotContainsString('REFERENCES', $migration);
        self::assertStringNotContainsString('CASCADE', $migration);
    }
}
