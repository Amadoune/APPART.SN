<?php

namespace Tests\PostgreSQL\AuthoringPublicFactHandoff;

use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPublicFactHandoff;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAuthoringPublicFactHandoffTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->connection->exec("INSERT INTO listing_lifecycle.listings (id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES ('91000000-0000-4000-8000-000000000001','92000000-0000-4000-8000-000000000001','draft',TIMESTAMPTZ '2026-08-09 10:00:00+00',0,0)");
    }

    protected function tearDown(): void
    {
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_candidate_is_idempotent_and_seals_as_immutable_public_fact(): void
    {
        $repository = new PostgreSqlAuthoringPublicFactHandoff($this->connection);
        $snapshot = new AuthoringPublicFactSnapshot('91000000-0000-4000-8000-000000000001', 2, PublicTransactionKind::Sale, '93000000-0000-4000-8000-000000000001', str_repeat('a', 64), new DateTimeImmutable('2026-08-09T10:01:00+00:00'));

        self::assertSame(PublicFactHandoffResult::Applied, $repository->prepare($snapshot));
        self::assertSame(PublicFactHandoffResult::AlreadyApplied, $repository->prepare($snapshot));
        self::assertSame(PublicTransactionKind::Sale, $repository->candidate($snapshot->listingId)?->transactionKind);
        self::assertSame(PublicFactHandoffResult::Applied, $repository->seal($snapshot->listingId, '94000000-0000-4000-8000-000000000001', new DateTimeImmutable('2026-08-09T10:03:00+00:00')));
        self::assertNull($repository->candidate($snapshot->listingId));
        self::assertSame(PublicTransactionKind::Sale, $repository->published($snapshot->listingId)?->transactionKind);
    }
}
