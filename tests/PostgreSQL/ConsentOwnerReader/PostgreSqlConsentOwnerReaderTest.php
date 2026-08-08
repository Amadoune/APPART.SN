<?php

namespace Tests\PostgreSQL\ConsentOwnerReader;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerReader\OwnerLeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentResultV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentStatusV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlConsentOwnerReaderTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlConsentOwnerSource $source;

    private OwnerLeadContactConsentReaderV1 $reader;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->source = new PostgreSqlConsentOwnerSource($this->connection, new ConsentOwnerSourceMapper);
        $this->reader = new OwnerLeadContactConsentReaderV1(
            new DeterministicConsentOwnerSourceRuntimeReadV1(
                $this->source,
                new DeterministicConsentOwnerSourceRuntimeReadPolicy,
            ),
        );
    }

    public function test_reader_propagates_all_owner_runtime_results_unchanged(): void
    {
        self::assertSame(LeadContactConsentStatusV1::Missing, $this->read('07:59')->status);
        $this->source->append($this->state(1, ConsentRevisionDecision::Granted, '08:00'));
        $this->source->append($this->state(2, ConsentRevisionDecision::Withdrawn, '09:00'));
        self::assertSame(LeadContactConsentStatusV1::Granted, $this->read('08:30')->status);
        self::assertSame(LeadContactConsentStatusV1::Denied, $this->read('09:30')->status);

        $this->connection->exec("UPDATE contacts_leads.consent_decision_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(LeadContactConsentStatusV1::Corrupted, $this->read('09:30')->status);
        $this->connection->exec('DROP TABLE contacts_leads.consent_decision_revisions');
        self::assertSame(LeadContactConsentStatusV1::DependencyUnavailable, $this->read('09:30')->status);
    }

    private function read(string $time): LeadContactConsentResultV1
    {
        return $this->reader->read(
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
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005406');
    }
}
