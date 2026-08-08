<?php

namespace Tests\PostgreSQL\ExperienceAcceptanceRuntime;

use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntime;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\ExperienceAcceptanceRuntimeAvailability;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\PostgreSql\PostgreSqlExperienceAcceptanceOwnerSource;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlExperienceAcceptanceRuntimeTest extends TestCase
{
    public function test_runtime_reports_the_real_owner_source_as_technically_available(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        $migration = dirname(__DIR__, 3).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/090_experience_acceptance_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlExperienceAcceptanceOwnerSource($connection, new ExperienceAcceptanceOwnerSourceMapper);
        $runtime = new DeterministicExperienceAcceptanceRuntime(new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy($source));
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
