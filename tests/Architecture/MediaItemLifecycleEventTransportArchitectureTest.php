<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class MediaItemLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_transport_foundation_has_no_execution_infrastructure(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/MediaItemLifecycleEventTransport';
        $contents = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach (['PostgreSql', 'PDO', 'Laravel', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Http', 'MediaCollectionRegistry', 'collectionId', 'replacementMediaId', 'caption', 'url', 'mediaOrder'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_no_runtime_binding_or_concrete_router_was_added(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/AppServiceProvider.php');
        self::assertStringNotContainsString('MediaItemLifecycleEventRouter', $provider);
        self::assertSame(9, count(glob(dirname(__DIR__, 2).'/app/Application/MediaItemLifecycleEventTransport/*.php') ?: []));
    }
}
