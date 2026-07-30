<?php

namespace Tests\PostgreSQL\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PersistentListingIntegrity;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

final class PostgreSqlListingRepositoryIntegrationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlListingRegistryHarness $harness;

    private PostgreSqlListingRepository $repository;

    private FakeListingRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->harness = new PostgreSqlListingRegistryHarness;
        $registry = $this->harness->freshRegistry();
        self::assertInstanceOf(PostgreSqlListingRepository::class, $registry);
        $this->repository = $registry;
        $this->connection = $this->harness->independentConnection();
        $this->fixtures = new FakeListingRegistryHarness;
    }

    public function test_add_and_save_persist_complete_ordered_history_without_consuming_events(): void
    {
        $listing = $this->fixtures->minimalListing();
        $this->repository->add($listing);
        self::assertNotEmpty($listing->releaseEvents());
        $loaded = $this->repository->find($listing->id());
        self::assertNotNull($loaded);
        $this->fixtures->mutate($loaded);
        $this->repository->save($loaded, 0);

        self::assertSame(1, $this->countRows('listing_lifecycle.listings'));
        self::assertSame(2, $this->countRows('listing_lifecycle.listing_revisions'));
        self::assertSame([1, 2], $this->connection->query('SELECT sequence FROM listing_lifecycle.listing_revisions ORDER BY sequence')->fetchAll(PDO::FETCH_COLUMN));
        self::assertNotEmpty($loaded->releaseEvents());
        self::assertSame([], $this->repository->find($listing->id())?->releaseEvents());
    }

    public function test_controlled_add_failure_rolls_back_root_and_revisions(): void
    {
        $listing = $this->fixtures->minimalListing();
        $this->harness->failNextWrite($this->repository);
        try {
            $this->repository->add($listing);
            self::fail('The controlled add must fail.');
        } catch (PersistentListingIntegrity) {
            self::assertSame(0, $this->countRows('listing_lifecycle.listings'));
            self::assertSame(0, $this->countRows('listing_lifecycle.listing_revisions'));
        }
    }

    public function test_controlled_save_failure_rolls_back_root_and_new_revision(): void
    {
        $listing = $this->fixtures->minimalListing();
        $this->repository->add($listing);
        $loaded = $this->repository->find($listing->id());
        self::assertNotNull($loaded);
        $this->fixtures->mutate($loaded);
        $this->harness->failNextWrite($this->repository);
        try {
            $this->repository->save($loaded, 0);
            self::fail('The controlled save must fail.');
        } catch (ConcurrentListingModification) {
            self::assertSame(0, $this->repository->find($listing->id())?->version());
            self::assertSame(1, $this->countRows('listing_lifecycle.listing_revisions'));
        }
    }

    public function test_database_enforces_foreign_key_local_identity_and_sequence(): void
    {
        $listing = $this->fixtures->minimalListing();
        $this->repository->add($listing);
        $row = $this->connection->query('SELECT * FROM listing_lifecycle.listing_revisions LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);

        foreach (['foreign' => ['listing_id' => '32000000-0000-4000-8000-000000000999'], 'identity' => ['sequence' => 2], 'sequence' => ['revision_id' => '32000000-0000-4000-8000-000000000999']] as $changes) {
            $candidate = array_replace($row, $changes);
            try {
                $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.listing_revisions (listing_id, sequence, revision_id, previous_status, status, actor_id, trigger, reason, origin, occurred_at, occurred_at_offset) VALUES (:listing_id, :sequence, :revision_id, :previous_status, :status, :actor_id, :trigger, :reason, :origin, :occurred_at, :occurred_at_offset)');
                $statement->execute(array_intersect_key($candidate, array_flip(['listing_id', 'sequence', 'revision_id', 'previous_status', 'status', 'actor_id', 'trigger', 'reason', 'origin', 'occurred_at', 'occurred_at_offset'])));
                self::fail('A durable Listing revision constraint must reject the row.');
            } catch (PDOException) {
                self::assertSame(1, $this->countRows('listing_lifecycle.listing_revisions'));
            }
        }
    }

    public function test_durable_history_rewrite_is_refused_and_root_is_unchanged(): void
    {
        $listing = $this->fixtures->minimalListing();
        $this->repository->add($listing);
        $this->connection->exec("UPDATE listing_lifecycle.listing_revisions SET reason = 'Durable tampering for integration proof.'");
        $this->fixtures->mutate($listing);

        $this->expectException(PersistentListingIntegrity::class);
        try {
            $this->repository->save($listing, 0);
        } finally {
            self::assertSame(0, (int) $this->connection->query('SELECT version FROM listing_lifecycle.listings')->fetchColumn());
            self::assertSame(1, $this->countRows('listing_lifecycle.listing_revisions'));
        }
    }

    public function test_archived_is_reconstructed_and_identity_remains_reserved(): void
    {
        $archived = $this->fixtures->archivedListing();
        $this->repository->add($archived);

        self::assertSame('archived', $this->repository->find($archived->id())?->status()->value);
        self::assertSame(1, $this->countRows('listing_lifecycle.listings'));
    }

    public function test_expired_and_withdrawn_are_persisted_without_becoming_terminal(): void
    {
        $expired = $this->fixtures->expiredListing();
        $withdrawn = $this->fixtures->withdrawnListing($this->fixtures->distinctId());
        $this->repository->add($expired);
        $this->repository->add($withdrawn);

        self::assertSame('expired', $this->repository->find($expired->id())?->status()->value);
        self::assertSame('withdrawn', $this->repository->find($withdrawn->id())?->status()->value);
        self::assertNotNull($this->repository->find($expired->id())?->expirationDate());
        self::assertNotNull($this->repository->find($withdrawn->id())?->expirationDate());
        $loadedExpired = $this->repository->find($expired->id());
        $loadedWithdrawn = $this->repository->find($withdrawn->id());
        self::assertNotNull($loadedExpired);
        self::assertNotNull($loadedWithdrawn);
        $loadedExpired->withdraw($this->revision(91), $this->evidence(TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $loadedWithdrawn->archive($this->revision(92), $this->evidence(TransitionTrigger::ReactivationDeadlineReached, TransitionOrigin::System), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $this->repository->save($loadedExpired, $expired->version());
        $this->repository->save($loadedWithdrawn, $withdrawn->version());
        self::assertSame('withdrawn', $this->repository->find($expired->id())?->status()->value);
        self::assertSame('archived', $this->repository->find($withdrawn->id())?->status()->value);
        self::assertSame(2, $this->countRows('listing_lifecycle.listings'));
    }

    private function revision(int $suffix): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('32000000-0000-4000-8000-%012d', $suffix));
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor:integration'), $trigger, TransitionReason::fromString('Persistence must not invent terminal state.'), $origin, new \DateTimeImmutable('2026-07-17T10:06:00+00:00'));
    }

    private function countRows(string $table): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM '.$table)->fetchColumn();
    }
}
