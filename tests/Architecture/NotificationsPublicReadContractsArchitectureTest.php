<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class NotificationsPublicReadContractsArchitectureTest extends TestCase
{
    #[Test]
    public function contracts_are_owner_scoped_read_only_and_backend_agnostic(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/Notifications/Application/PublicRead';
        self::assertDirectoryExists($directory);
        $sources = '';

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach (['IdentityAccess', 'Moderation', 'Reservation', 'ContentSeo', 'SearchDiscovery', 'Transport', 'Infrastructure', 'Persistence', 'Runtime', 'Controller', 'Http', 'Event', 'Delivery', 'Outbox', 'PDO', 'PostgreSQL', 'function now('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertSame(3, substr_count($sources, 'interface '));
        self::assertSame(3, substr_count($sources, 'enum '));
        self::assertSame(5, substr_count($sources, 'final readonly class '));
        self::assertSame(3, substr_count($sources, 'public function read('));
    }
}
