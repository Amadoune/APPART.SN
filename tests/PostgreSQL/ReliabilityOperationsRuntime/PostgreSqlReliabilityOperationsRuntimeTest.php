<?php

namespace Tests\PostgreSQL\ReliabilityOperationsRuntime;

use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntime;
use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntimeAvailabilityPolicy;
use Appart\Modules\ReliabilityOperations\Application\Runtime\ReliabilityOperationsRuntimeAvailability;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\PostgreSql\PostgreSqlReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\ReliabilityOperationsOwnerSourceMapper;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReliabilityOperationsRuntimeTest extends TestCase
{
    public function test_runtime_reports_the_real_owner_source_as_technically_available(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        $migration = dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/088_reliability_operations_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlReliabilityOperationsOwnerSource($connection, new ReliabilityOperationsOwnerSourceMapper);
        $runtime = new DeterministicReliabilityOperationsRuntime(new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy($source));
        self::assertSame(ReliabilityOperationsRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(ReliabilityOperationsRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
