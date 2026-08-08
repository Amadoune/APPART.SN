<?php

namespace Tests\PostgreSQL\AntiAbuseOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAntiAbuseOwnerSourceRuntimeReadTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAntiAbuseOwnerSource $source;

    private DeterministicLeadIngressAntiAbuseRuntimeReadV1 $runtime;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE contacts_leads.anti_abuse_decision_revisions');
        $this->source = new PostgreSqlAntiAbuseOwnerSource($this->connection, new AntiAbuseOwnerSourceMapper);
        $this->runtime = new DeterministicLeadIngressAntiAbuseRuntimeReadV1($this->source, new DeterministicLeadIngressAntiAbuseRuntimeReadPolicy);
    }

    public function test_temporal_read_missing_and_corruption_are_deterministic(): void
    {
        $this->source->append($this->state(1, AntiAbuseRevisionDecision::Allowed, '08:00'));
        $this->source->append($this->state(2, AntiAbuseRevisionDecision::Blocked, '09:00'));

        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::Missing, $this->read('07:59')->status);
        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::Allowed, $this->read('08:30')->status);
        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::Blocked, $this->read('09:30')->status);
        $this->connection->exec("UPDATE contacts_leads.anti_abuse_decision_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::Corrupted, $this->read('09:30')->status);
    }

    public function test_unavailability_and_recovery_are_fail_closed(): void
    {
        $this->connection->exec('DROP TABLE contacts_leads.anti_abuse_decision_revisions');
        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::DependencyUnavailable, $this->read('09:30')->status);
        $this->migrate();
        self::assertSame(LeadIngressAntiAbuseRuntimeReadStatus::Missing, $this->read('09:30')->status);
    }

    private function read(string $time): LeadIngressAntiAbuseRuntimeReadResult
    {
        return $this->runtime->read(self::intent(), new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T'.$time.':00Z')));
    }

    private function state(int $revision, AntiAbuseRevisionDecision $decision, string $time): AntiAbuseRevisionState
    {
        $at = new DateTimeImmutable('2026-07-31T'.$time.':00Z');

        return new AntiAbuseRevisionState(self::intent(), $revision, $decision, $at, $at, 'anti-abuse-v1');
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005411');
    }

    private function migrate(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'073_anti_abuse_owner_local_source.sql'));
    }
}
