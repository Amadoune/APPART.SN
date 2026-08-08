<?php

namespace Tests\PostgreSQL\AntiAbuseOwnerReader;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerReader\OwnerLeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAntiAbuseOwnerReaderTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAntiAbuseOwnerSource $source;

    private OwnerLeadIngressAntiAbuseReaderV1 $reader;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE contacts_leads.anti_abuse_decision_revisions');
        $this->source = new PostgreSqlAntiAbuseOwnerSource($this->connection, new AntiAbuseOwnerSourceMapper);
        $this->reader = new OwnerLeadIngressAntiAbuseReaderV1(
            new DeterministicLeadIngressAntiAbuseRuntimeReadV1($this->source, new DeterministicLeadIngressAntiAbuseRuntimeReadPolicy),
        );
    }

    public function test_reader_propagates_all_runtime_read_results_mechanically(): void
    {
        self::assertSame(LeadIngressAntiAbuseStatusV1::Missing, $this->read('07:59')->status);
        $this->source->append($this->state(1, AntiAbuseRevisionDecision::Allowed, '08:00'));
        $this->source->append($this->state(2, AntiAbuseRevisionDecision::Blocked, '09:00'));
        self::assertSame(LeadIngressAntiAbuseStatusV1::Allowed, $this->read('08:30')->status);
        self::assertSame(LeadIngressAntiAbuseStatusV1::Blocked, $this->read('09:30')->status);
        $this->connection->exec("UPDATE contacts_leads.anti_abuse_decision_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(LeadIngressAntiAbuseStatusV1::Corrupted, $this->read('09:30')->status);
        $this->connection->exec('DROP TABLE contacts_leads.anti_abuse_decision_revisions');
        self::assertSame(LeadIngressAntiAbuseStatusV1::DependencyUnavailable, $this->read('09:30')->status);
    }

    private function read(string $time): LeadIngressAntiAbuseResultV1
    {
        return $this->reader->read(self::intent(), new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T'.$time.':00Z')));
    }

    private function state(int $revision, AntiAbuseRevisionDecision $decision, string $time): AntiAbuseRevisionState
    {
        $at = new DateTimeImmutable('2026-07-31T'.$time.':00Z');

        return new AntiAbuseRevisionState(self::intent(), $revision, $decision, $at, $at, 'anti-abuse-v1');
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005414');
    }

    private function migrate(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'073_anti_abuse_owner_local_source.sql'));
    }
}
