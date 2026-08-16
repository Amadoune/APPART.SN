<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ActiveGenerationBootstrapArchitectureTest extends TestCase
{
    public function test_application_owner_has_no_sql_cross_domain_write_or_auto_bootstrap(): void
    {
        $root = dirname(__DIR__, 3);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app/Application/ActiveGenerationBootstrap'));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            self::assertIsString($content);
            self::assertStringNotContainsString('PDO', $content);
            self::assertStringNotContainsString('INSERT ', $content);
            self::assertStringNotContainsString('UPDATE ', $content);
            self::assertStringNotContainsString('DELETE ', $content);
            self::assertStringNotContainsString('ProjectPublishedListing', $content);
        }
        self::assertDirectoryDoesNotExist($root.'/app/Infrastructure/ActiveGenerationBootstrap/Migrations');
        $provider = file_get_contents($root.'/app/Providers/ActiveGenerationBootstrapServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringNotContainsString('function boot', $provider);
        self::assertStringNotContainsString('->bootstrap(', $provider);
    }
}
