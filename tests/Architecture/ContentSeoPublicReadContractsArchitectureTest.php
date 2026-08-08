<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ContentSeoPublicReadContractsArchitectureTest extends TestCase
{
    #[Test]
    public function contracts_are_owner_scoped_read_only_and_backend_agnostic(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/PublicRead';
        self::assertDirectoryExists($directory);
        $sources = '';

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach (['ListingLifecycle', 'Media\\', 'Geography', 'SearchDiscovery', 'IdentityAccess', 'Illuminate\\', 'Laravel', 'PDO', 'PostgreSQL', 'SQL', 'Persistence', 'Projection', 'Runtime', 'Provider', 'Controller', 'Event', 'Delivery', 'Outbox', 'function now('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertSame(2, substr_count($sources, 'interface '));
        self::assertSame(2, substr_count($sources, 'enum '));
        self::assertSame(4, substr_count($sources, 'final readonly class '));
        self::assertSame(2, substr_count($sources, 'public function read('));
    }
}
