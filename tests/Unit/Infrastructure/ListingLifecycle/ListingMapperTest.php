<?php

namespace Tests\Unit\Infrastructure\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingRevisionSnapshot;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingSnapshot;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PersistentListingIntegrity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

final class ListingMapperTest extends TestCase
{
    private ListingMapper $mapper;

    private FakeListingRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->mapper = new ListingMapper;
        $this->fixtures = new FakeListingRegistryHarness;
    }

    #[DataProvider('aggregateProvider')]
    public function test_round_trip_preserves_every_observable_property(string $fixture): void
    {
        $expected = $this->fixtures->{$fixture}();
        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->propertyId()->value, $actual->propertyId()->value);
        self::assertSame($expected->status(), $actual->status());
        self::assertSame($expected->version(), $actual->version());
        self::assertEquals($expected->expirationDate(), $actual->expirationDate());
        self::assertEquals($expected->revisions(), $actual->revisions());
        self::assertSame([], $actual->releaseEvents());
    }

    public function test_exact_revision_order_dates_and_microseconds_are_preserved(): void
    {
        $snapshot = $this->mapper->toSnapshot($this->fixtures->listingWithHistory());

        self::assertSame([1, 2, 3], array_map(static fn (ListingRevisionSnapshot $revision): int => $revision->sequence, $snapshot->revisions));
        self::assertSame(['draft', 'submitted', 'under_review'], array_map(static fn (ListingRevisionSnapshot $revision): string => $revision->status, $snapshot->revisions));
        self::assertSame('2026-07-17T10:02:00.000000+00:00', $snapshot->lastChangedAt);
        self::assertSame(2, $snapshot->version);
    }

    public function test_next_mutation_after_reconstruction_uses_the_next_version(): void
    {
        $listing = $this->mapper->toAggregate($this->mapper->toSnapshot($this->fixtures->minimalListing()));
        $this->fixtures->mutate($listing);

        self::assertSame(1, $listing->version());
        self::assertSame(ListingStatus::Submitted, $listing->status());
        self::assertNotEmpty($listing->releaseEvents());
    }

    #[DataProvider('invalidSnapshotProvider')]
    public function test_invalid_snapshots_are_refused(string $mutation): void
    {
        $snapshot = $this->mapper->toSnapshot($this->fixtures->listingWithHistory());
        $invalid = match ($mutation) {
            'status' => $this->copy($snapshot, status: 'unknown'),
            'version' => $this->copy($snapshot, version: -1),
            'identity' => $this->copy($snapshot, id: 'invalid'),
            'empty' => $this->copy($snapshot, revisions: []),
            'duplicate' => $this->copy($snapshot, revisions: [$snapshot->revisions[0], new ListingRevisionSnapshot(2, $snapshot->revisions[0]->id, 'draft', 'submitted', 'actor:listing-contract', 'submission_confirmed', 'Fixed evidence for the shared Listing Registry contract.', 'advertiser', '2026-07-17T10:01:00.000000+00:00')]),
            'sequence' => $this->copy($snapshot, revisions: [new ListingRevisionSnapshot(2, ...array_slice((array) $snapshot->revisions[0], 1))]),
            'chain' => $this->copy($snapshot, status: 'archived'),
        };

        $this->expectException(PersistentListingIntegrity::class);
        $this->mapper->toAggregate($invalid);
    }

    public static function aggregateProvider(): array
    {
        return [['minimalListing'], ['listingWithHistory'], ['archivedListing'], ['expiredListing'], ['withdrawnListing']];
    }

    public static function invalidSnapshotProvider(): array
    {
        return array_map(static fn (string $case): array => [$case], ['status', 'version', 'identity', 'empty', 'duplicate', 'sequence', 'chain']);
    }

    /** @param list<ListingRevisionSnapshot>|null $revisions */
    private function copy(ListingSnapshot $snapshot, ?string $id = null, ?string $status = null, ?int $version = null, ?array $revisions = null): ListingSnapshot
    {
        return new ListingSnapshot($id ?? $snapshot->id, $snapshot->propertyId, $status ?? $snapshot->status, $snapshot->lastChangedAt, $snapshot->expirationDate, $version ?? $snapshot->version, $revisions ?? $snapshot->revisions);
    }
}
