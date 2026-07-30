<?php

namespace Tests\PostgreSQL\ProjectionRebuildRuntimeSource;

use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Infrastructure\ProjectionRebuildRuntimeSource\PostgreSql\PostgreSqlPublicProjectionRebuildEnumerator;
use App\Infrastructure\ProjectionRebuildRuntimeSource\RebuildCheckpointCodec;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicProjectionRebuildEnumeratorTest extends TestCase
{
    private const string A = '9a000000-0000-4000-8000-000000000001';

    private const string B = '9a000000-0000-4000-8000-000000000002';

    private const string C = '9a000000-0000-4000-8000-000000000003';

    private PDO $connection;

    private PostgreSqlPublicProjectionRebuildEnumerator $enumerator;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $statement = $this->connection->prepare("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES (:id,'9b000000-0000-4000-8000-000000000001','draft','2026-07-19T10:00:00+00:00',0,0)");
        foreach ([self::C, self::A, self::B] as $id) {
            $statement->execute(['id' => $id]);
        }
        $this->enumerator = new PostgreSqlPublicProjectionRebuildEnumerator($this->connection, new RebuildCheckpointCodec);
    }

    public function test_full_is_keyset_paginated_replayable_and_resumable(): void
    {
        $scope = PublicProjectionRebuildScope::full();
        $first = $this->enumerator->page($scope, null, 2);
        self::assertSame([self::A, self::B], $first->listingIds);
        self::assertNotNull($first->nextCheckpoint);
        self::assertEquals($first, $this->enumerator->page($scope, null, 2));

        $second = $this->enumerator->page($scope, $first->nextCheckpoint, 2);
        self::assertSame([self::C], $second->listingIds);
        self::assertNull($second->nextCheckpoint);
    }

    public function test_explicit_listings_are_deduplicated_ordered_and_paged_without_database_filtering(): void
    {
        $missing = '9a000000-0000-4000-8000-000000000009';
        $scope = PublicProjectionRebuildScope::listings([$missing, self::A, $missing]);
        $first = $this->enumerator->page($scope, null, 1);
        self::assertSame([self::A], $first->listingIds);
        self::assertNotNull($first->nextCheckpoint);
        self::assertSame([$missing], $this->enumerator->page($scope, $first->nextCheckpoint, 1)->listingIds);
    }

    public function test_range_is_inclusive_bounded_and_never_extended(): void
    {
        $page = $this->enumerator->page(PublicProjectionRebuildScope::range(self::B, self::C), null, 10);

        self::assertSame([self::B, self::C], $page->listingIds);
        self::assertNull($page->nextCheckpoint);
    }

    public function test_checkpoint_is_opaque_and_bound_to_its_scope(): void
    {
        $full = PublicProjectionRebuildScope::full();
        $checkpoint = $this->enumerator->page($full, null, 1)->nextCheckpoint;
        self::assertNotNull($checkpoint);

        $this->expectException(InvalidArgumentException::class);
        $this->enumerator->page(PublicProjectionRebuildScope::range(self::A, self::C), $checkpoint, 1);
    }

    public function test_invalid_database_bounds_are_rejected_explicitly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->enumerator->page(PublicProjectionRebuildScope::range('invalid:a', 'invalid:z'), null, 10);
    }
}
