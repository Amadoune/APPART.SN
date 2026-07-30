<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AccountStatusDeliveryConsumptionArchitectureTest extends TestCase
{
    public function test_consumption_has_no_persistence_delivery_runtime_or_http(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/AccountStatusEventConsumption';
        $source = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $source .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO', 'PostgreSql', 'Illuminate', 'Laravel', 'Inbox', 'Outbox',
            'Worker', 'Http', 'publish(', 'dispatch(', 'send(', '->route(',
            'AccountStatusWorkflow', 'AccountRegistry',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_consumer_signature_is_strictly_typed(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AccountStatusEventConsumption/AccountStatusDeliveryConsumer.php',
        );

        self::assertStringContainsString('AccountStatusDeliveryMessage $message', $source);
        self::assertStringContainsString('AccountStatusRoutingDestination $destination', $source);
        self::assertStringContainsString(': AccountStatusConsumptionResult', $source);
    }
}
