<?php

namespace Tests\PostgreSQL\AntiAbuseOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\AntiAbuseOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAntiAbuseOwnerSourceRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE contacts_leads.anti_abuse_decision_revisions');
    }

    public function test_runtime_reports_availability_unavailability_recovery_and_corruption(): void
    {
        $runtime = $this->runtime();
        self::assertSame(AntiAbuseOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec('DROP TABLE contacts_leads.anti_abuse_decision_revisions');
        self::assertSame(AntiAbuseOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());

        $this->migrate();
        self::assertSame(AntiAbuseOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec("INSERT INTO contacts_leads.anti_abuse_decision_revisions (lead_ingress_intent_id, revision, decision, effective_at, recorded_at, policy_reference, revision_checksum) VALUES ('00000000-0000-4000-8000-000000005408', 1, 'allowed', '2026-07-31 08:00:00+00', '2026-07-31 08:00:00+00', 'anti-abuse-v1', repeat('0', 64))");
        self::assertSame(AntiAbuseOwnerSourceRuntimeAvailability::Corrupted, $runtime->availability());
    }

    private function runtime(): DeterministicAntiAbuseOwnerSourceRuntimeV1
    {
        return new DeterministicAntiAbuseOwnerSourceRuntimeV1(
            new DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy(
                new PostgreSqlAntiAbuseOwnerSource($this->connection, new AntiAbuseOwnerSourceMapper),
            ),
        );
    }

    private function migrate(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'073_anti_abuse_owner_local_source.sql'));
    }
}
