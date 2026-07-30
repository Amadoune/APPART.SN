<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusOutboxOwnerAuditArchitectureTest extends TestCase
{
    public function test_owner_decision_remains_the_source_of_the_versioned_implementation(): void
    {
        $root = dirname(__DIR__, 2);
        $specification = (string) file_get_contents($root.'/docs/ACCOUNT-STATUS-OUTBOX-OWNER-SPECIFICATION.md');
        $compatibility = (string) file_get_contents($root.'/docs/ACCOUNT-STATUS-OUTBOX-OWNER-COMPATIBILITY-MATRIX.md');

        self::assertStringContainsString('IdentityAccess', $specification);
        self::assertStringContainsString('identity_access', $specification);
        self::assertStringContainsString('AccountStatus', $specification);
        self::assertStringContainsString('account.status.*', $specification);
        self::assertStringContainsString('J5 BLOQUANT', $compatibility);
        self::assertFileExists(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/043_identity_access_outbox_owner.sql',
        );
        self::assertFileExists(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/043_identity_access_outbox_owner.down.sql',
        );
    }
}
