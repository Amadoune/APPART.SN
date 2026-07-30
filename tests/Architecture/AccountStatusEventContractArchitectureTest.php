<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AccountStatusEventContractArchitectureTest extends TestCase
{
    public function test_contract_is_application_only_and_has_no_future_infrastructure(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountStatusEvent';
        $source = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $source .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO', 'PostgreSql', 'Illuminate', 'Laravel', 'Transport', 'Routing',
            'Inbox', 'Outbox', 'Publisher', 'Consumer', 'Worker', 'Http',
            'AccountRegistry', 'SensitivePersistenceValueV1', 'Credential',
            'VerificationToken', 'RoleAssignment', 'Consent',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_catalog_and_payload_versions_are_closed(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountStatusEvent';
        $types = (string) file_get_contents($root.'/AccountStatusEventType.php');
        $versions = (string) file_get_contents($root.'/AccountStatusEventPayloadVersion.php');

        self::assertSame(2, substr_count($types, 'case '));
        self::assertSame(1, substr_count($versions, 'case '));
        self::assertStringContainsString('case V1 = 1;', $versions);
    }
}
