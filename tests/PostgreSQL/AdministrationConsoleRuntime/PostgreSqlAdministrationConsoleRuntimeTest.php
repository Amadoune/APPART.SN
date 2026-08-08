<?php

namespace Tests\PostgreSQL\AdministrationConsoleRuntime;

use Appart\Modules\AdministrationConsole\Application\Runtime\AdministrationConsoleRuntimeAvailability;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntime;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntimeAvailabilityPolicy;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationConsoleOwnerSource;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrationConsoleRuntimeTest extends TestCase
{
    public function test_runtime_reports_real_owner_source_availability_without_business_decision(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlAdministrationConsoleOwnerSource($connection, new AdministrationConsoleOwnerSourceMapper);
        $runtime = new DeterministicAdministrationConsoleRuntime(new DeterministicAdministrationConsoleRuntimeAvailabilityPolicy($source));

        self::assertSame(AdministrationConsoleRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(AdministrationConsoleRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
