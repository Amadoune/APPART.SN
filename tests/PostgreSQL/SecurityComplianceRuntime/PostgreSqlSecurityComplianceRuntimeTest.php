<?php

namespace Tests\PostgreSQL\SecurityComplianceRuntime;

use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntime;
use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntimeAvailabilityPolicy;
use Appart\Modules\SecurityCompliance\Application\Runtime\SecurityComplianceRuntimeAvailability;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\PostgreSql\PostgreSqlSecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSecurityComplianceRuntimeTest extends TestCase
{
    public function test_runtime_reports_the_real_owner_source_as_technically_available(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        $migration = dirname(__DIR__, 3).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlSecurityComplianceOwnerSource($connection, new SecurityComplianceOwnerSourceMapper);
        $runtime = new DeterministicSecurityComplianceRuntime(new DeterministicSecurityComplianceRuntimeAvailabilityPolicy($source));
        self::assertSame(SecurityComplianceRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(SecurityComplianceRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
