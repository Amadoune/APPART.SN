<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AccountStatusEventRoutingArchitectureTest extends TestCase
{
    public function test_routing_is_pure_and_has_no_delivery_infrastructure(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/AccountStatusEventRouting';
        $source = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $source .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO', 'PostgreSql', 'Illuminate', 'Laravel', 'Inbox', 'Outbox',
            'Consumer', 'Worker', 'Http', 'publish(', 'dispatch(', 'send(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_router_consumes_only_the_certified_delivery_message(): void
    {
        $router = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AccountStatusEventRouting/AccountStatusEventRouter.php',
        );

        self::assertStringContainsString('route(AccountStatusDeliveryMessage $message)', $router);
        self::assertStringContainsString(': AccountStatusRoutingResult', $router);
    }
}
