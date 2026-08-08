<?php

namespace Tests\PostgreSQL\LegacyMigrationRuntime;

use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntime;
use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntimeAvailabilityPolicy;
use Appart\Modules\LegacyMigration\Application\Runtime\LegacyMigrationRuntimeAvailability;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\PostgreSql\PostgreSqlLegacyMigrationOwnerSource;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLegacyMigrationRuntimeTest extends TestCase
{
    public function test_runtime_reports_the_real_owner_source_as_technically_available(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlLegacyMigrationOwnerSource($connection, new LegacyMigrationOwnerSourceMapper);
        $runtime = new DeterministicLegacyMigrationRuntime(new DeterministicLegacyMigrationRuntimeAvailabilityPolicy($source));

        self::assertSame(LegacyMigrationRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(LegacyMigrationRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
