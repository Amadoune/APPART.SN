<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListingCreationBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function application_creation_boundary_has_no_infrastructure_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/Creation';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('\\Infrastructure\\', $source, $file->getPathname());
            self::assertStringNotContainsString('Illuminate\\', $source, $file->getPathname());
            self::assertStringNotContainsString('PDO', $source, $file->getPathname());
        }
    }

    #[Test]
    public function migration_055_is_additive_and_owner_scoped(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/055_listing_creation_intents.sql');

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS listing_lifecycle.listing_creation_intents', $migration);
        self::assertStringNotContainsString('ALTER TABLE', $migration);
        self::assertStringNotContainsString('REFERENCES', $migration);
        self::assertStringNotContainsString('ON DELETE CASCADE', $migration);
    }
}
