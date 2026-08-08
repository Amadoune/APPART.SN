<?php

namespace Tests\PostgreSQL\ConsentOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\ConsentOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlConsentOwnerSourceRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
    }

    public function test_runtime_reports_available_and_fails_closed_when_dependency_disappears(): void
    {
        $runtime = $this->runtime();
        self::assertSame(ConsentOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec('DROP TABLE contacts_leads.consent_decision_revisions');
        self::assertSame(ConsentOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());

        PostgreSqlTestEnvironment::migrate($this->connection);
        self::assertSame(ConsentOwnerSourceRuntimeAvailability::Available, $runtime->availability());
    }

    private function runtime(): DeterministicConsentOwnerSourceRuntimeV1
    {
        $source = new PostgreSqlConsentOwnerSource($this->connection, new ConsentOwnerSourceMapper);

        return new DeterministicConsentOwnerSourceRuntimeV1(
            new DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy($source),
        );
    }
}
