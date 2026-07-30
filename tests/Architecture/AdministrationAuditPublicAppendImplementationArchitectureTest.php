<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AdministrationAuditPublicAppendImplementationArchitectureTest extends TestCase
{
    #[Test]
    public function implementation_is_owner_local_and_exposes_no_forbidden_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $implementation = (string) file_get_contents(
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrationAuditAppendV1.php',
        );
        $provider = (string) file_get_contents(
            $root.'/app/Providers/AdministrationAuditPublicAppendServiceProvider.php',
        );
        $migration = (string) file_get_contents(
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/071_administration_audit_public_append.sql',
        );

        foreach ([
            'ModerationReports\\', 'Controller', 'Route', 'Event', 'Delivery',
            'Consumer', 'Outbox', 'RuntimeHealth', 'Aggregate', 'Registry',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $implementation.$provider);
        }
        self::assertStringNotContainsString('FOREIGN KEY', $migration);
        self::assertStringNotContainsString('CASCADE', $migration);
        self::assertStringNotContainsString('TRIGGER', $migration);
        self::assertSame(
            1,
            substr_count(
                $provider,
                "PostgreSqlAdministrationAuditAppendV1::class,\n            AdministrationAuditAppendV1::class",
            ),
        );
    }
}
