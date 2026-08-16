<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicGeographyMaterializationArchitectureTest extends TestCase
{
    public function test_application_materialization_has_no_sql_or_cross_domain_write_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/app/Application/PublicGeography{Materialization,Refresh}/*.php', GLOB_BRACE) ?: [];
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $source = strtolower((string) file_get_contents($file));
            self::assertStringNotContainsString('select ', $source, $file);
            self::assertStringNotContainsString('insert ', $source, $file);
            self::assertStringNotContainsString('searchdiscovery', $source, $file);
            self::assertStringNotContainsString('publicmediasource', $source, $file);
            self::assertStringNotContainsString('publiclistingprojectionwriter', $source, $file);
            self::assertStringNotContainsString('/geography/', $source, $file);
        }
    }

    public function test_no_materialization_migration_was_added(): void
    {
        self::assertSame([], glob(dirname(__DIR__, 2).'/database/migrations/*public*geography*material*') ?: []);
    }
}
