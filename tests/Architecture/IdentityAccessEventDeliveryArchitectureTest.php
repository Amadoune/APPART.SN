<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class IdentityAccessEventDeliveryArchitectureTest extends TestCase
{
    public function test_event_contracts_are_application_owned_and_framework_independent(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/IdentityAccess/Application/IdentityAccessEvent';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate\\', 'PDO', 'Outbox', 'Controller', 'Route::'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    public function test_5_1_g_introduces_no_http_or_outbox_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [
            $root.'/app/Application/IdentityAccessEventTransport',
            $root.'/app/Application/IdentityAccessEventRouting',
            $root.'/app/Application/IdentityAccessEventDelivery',
        ];
        foreach ($paths as $path) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                foreach (['Outbox', 'Controller', 'Middleware', 'Route::', 'Illuminate\\'] as $forbidden) {
                    self::assertStringNotContainsString($forbidden, $source);
                }
            }
        }
    }
}
