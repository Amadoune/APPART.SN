<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AccountStatusEventTransportArchitectureTest extends TestCase
{
    public function test_transport_has_no_routing_persistence_runtime_or_private_data(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/AccountStatusEventTransport';
        $source = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $source .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO', 'PostgreSql', 'Illuminate', 'Laravel', 'Routing', 'Inbox',
            'Outbox', 'Publisher', 'Consumer', 'Worker', 'Http', 'AccountRegistry',
            'SensitivePersistenceValueV1', 'VerificationToken', 'RoleAssignment',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_transport_version_is_closed_and_payload_implements_the_generic_port(): void
    {
        $root = dirname(__DIR__, 2);
        $version = (string) file_get_contents($root.'/app/Application/AccountStatusEventTransport/AccountStatusTransportVersion.php');
        $payload = (string) file_get_contents($root.'/app/Application/AccountStatusEventTransport/AccountStatusDeliveryPayload.php');

        self::assertSame(1, substr_count($version, 'case '));
        self::assertStringContainsString('case V1 = 1;', $version);
        self::assertStringContainsString('implements PublicProjectionDeliveryPayload', $payload);
    }
}
