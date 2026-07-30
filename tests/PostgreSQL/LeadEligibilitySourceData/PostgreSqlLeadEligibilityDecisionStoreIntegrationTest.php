<?php

namespace Tests\PostgreSQL\LeadEligibilitySourceData;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterializationResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadEligibilityDecisionStoreIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_corruption_is_explicit_and_never_becomes_missing(): void
    {
        $this->store()->materialize($this->lot());
        $this->connection->exec("UPDATE contacts_leads.lead_eligibility_decisions SET materialization_checksum='".str_repeat('0', 64)."'");

        self::assertSame(LeadEligibilitySourceReadStatus::Corrupted, $this->store()->current($this->listingId())->status);
    }

    public function test_migration_rollback_and_current_index_are_certified(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = (string) file_get_contents($root.'023_lead_eligibility_source_data.down.sql');
        $up = (string) file_get_contents($root.'023_lead_eligibility_source_data.sql');
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('contacts_leads.lead_eligibility_decisions')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('contacts_leads.lead_eligibility_decisions', $this->connection->query("SELECT to_regclass('contacts_leads.lead_eligibility_decisions')")->fetchColumn());

        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT * FROM contacts_leads.lead_eligibility_decisions WHERE listing_id=:listing_id ORDER BY version DESC LIMIT 1');
        $statement->execute(['listing_id' => $this->listingId()->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));
        self::assertStringContainsString('lead_eligibility_current_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_external_transaction_rollback_preserves_atomicity(): void
    {
        $this->connection->beginTransaction();
        $this->store()->materialize($this->lot());
        $this->connection->rollBack();

        self::assertSame(LeadEligibilitySourceReadStatus::SourceAbsent, $this->store()->current($this->listingId())->status);
    }

    public function test_concurrent_identical_writes_converge(): void
    {
        self::assertSame(
            ['already_materialized', 'created'],
            $this->concurrent('identical'),
        );
        self::assertSame(1, $this->rowCount());
    }

    public function test_concurrent_relation_divergence_is_rejected(): void
    {
        self::assertSame(
            ['created', 'relation_divergence'],
            $this->concurrent('divergent'),
        );
        self::assertSame(1, $this->rowCount());
    }

    public function test_concurrent_successive_versions_preserve_continuity(): void
    {
        self::assertSame(LeadEligibilityMaterializationResult::Created, $this->store()->materialize($this->lot()));
        self::assertSame(['created', 'created'], $this->concurrent('successive'));
        self::assertSame(3, $this->rowCount());
        self::assertSame([1, 2, 3], array_map(static fn ($record): int => $record->revision->version, $this->store()->history($this->listingId())));
    }

    /** @return list<string> */
    private function concurrent(string $mode): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-lead-eligibility-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number, $mode], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start lead eligibility worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException('Lead eligibility worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }

    private function rowCount(): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM contacts_leads.lead_eligibility_decisions WHERE listing_id='b4400000-0000-4000-8000-000000000001'")->fetchColumn();
    }

    private function store(): PostgreSqlLeadEligibilityDecisionStore
    {
        return new PostgreSqlLeadEligibilityDecisionStore($this->connection, new LeadEligibilitySourceDataMapper);
    }

    private function lot(): LeadEligibilityMaterialization
    {
        $revision = new EligibilityRevision('b4600000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new DateTimeImmutable('2026-07-22T10:00:00+00:00')));
        $advertiser = AdvertiserId::fromString('b4500000-0000-4000-8000-000000000001');

        return new LeadEligibilityMaterialization($this->listingId(), $advertiser, ListingContactability::Contactable, $revision, $advertiser, AdvertiserEligibility::EligibleRecipient, $revision);
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('b4400000-0000-4000-8000-000000000001');
    }
}
