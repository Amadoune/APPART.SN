<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_transport_foundation_has_no_execution_or_persistence_infrastructure(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PlaceLifecycleEventTransport';
        $contents = '';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PostgreSql',
            'PDO',
            'Illuminate',
            'Laravel',
            'Inbox',
            'Outbox',
            'Repository',
            'Publisher',
            'Consumer',
            'Worker',
            'Http',
            'PlaceRegistry',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_only_the_pure_router_port_exists(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PlaceLifecycleEventTransport';

        self::assertFileExists($root.'/PlaceLifecycleEventRouter.php');
        self::assertFileDoesNotExist($root.'/DeterministicPlaceLifecycleEventRouter.php');
        self::assertFileDoesNotExist($root.'/PostgreSqlPlaceLifecycleEventRouter.php');
    }
}
