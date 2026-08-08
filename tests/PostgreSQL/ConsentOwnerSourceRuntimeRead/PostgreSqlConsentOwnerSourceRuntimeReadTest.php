<?php

namespace Tests\PostgreSQL\ConsentOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlConsentOwnerSourceRuntimeReadTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlConsentOwnerSource $source;

    private DeterministicConsentOwnerSourceRuntimeReadV1 $runtime;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->source = new PostgreSqlConsentOwnerSource($this->connection, new ConsentOwnerSourceMapper);
        $this->runtime = new DeterministicConsentOwnerSourceRuntimeReadV1(
            $this->source,
            new DeterministicConsentOwnerSourceRuntimeReadPolicy,
        );
    }

    public function test_temporal_reduction_is_deterministic_and_fail_closed(): void
    {
        self::assertSame(ConsentRevisionWriteResult::Applied, $this->source->append($this->state(1, ConsentRevisionDecision::Granted, '08:00')));
        self::assertSame(ConsentRevisionWriteResult::Applied, $this->source->append($this->state(2, ConsentRevisionDecision::Withdrawn, '09:00')));

        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Missing, $this->read('07:59')->status);
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Granted, $this->read('08:30')->status);
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Denied, $this->read('09:30')->status);

        $this->connection->exec("UPDATE contacts_leads.consent_decision_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Corrupted, $this->read('09:30')->status);
    }

    public function test_dependency_unavailability_and_recovery_are_closed(): void
    {
        $this->connection->exec('DROP TABLE contacts_leads.consent_decision_revisions');
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable, $this->read('09:30')->status);

        PostgreSqlTestEnvironment::migrate($this->connection);
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Missing, $this->read('09:30')->status);
    }

    private function read(string $time): ConsentOwnerSourceRuntimeReadResult
    {
        return $this->runtime->read(
            self::intent(),
            new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T'.$time.':00Z')),
        );
    }

    private function state(int $revision, ConsentRevisionDecision $decision, string $time): ConsentRevisionState
    {
        $effectiveAt = new DateTimeImmutable('2026-07-31T'.$time.':00Z');

        return new ConsentRevisionState(
            self::intent(),
            $revision,
            $decision,
            $effectiveAt,
            $effectiveAt->modify('+1 minute'),
            'contact-v1',
        );
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005403');
    }
}
