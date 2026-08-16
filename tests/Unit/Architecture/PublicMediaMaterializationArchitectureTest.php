<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicMediaMaterializationArchitectureTest extends TestCase
{
    public function test_materialization_has_no_forbidden_owner_or_storage_dependencies(): void
    {
        $root = dirname(__DIR__, 3);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app/Application/PublicMediaMaterialization'));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            self::assertIsString($content);
            self::assertStringNotContainsString('PublicProjection', $content);
            self::assertStringNotContainsString('SearchDiscovery', $content);
            self::assertStringNotContainsString('PublicGeography', $content);
            self::assertStringNotContainsString('storageKey', $content);
            self::assertStringNotContainsString('https://appart', $content);
            self::assertStringNotContainsString('thumbnail', $content);
        }
        self::assertDirectoryDoesNotExist($root.'/app/Infrastructure/PublicMediaMaterialization/Migrations');
    }
}
