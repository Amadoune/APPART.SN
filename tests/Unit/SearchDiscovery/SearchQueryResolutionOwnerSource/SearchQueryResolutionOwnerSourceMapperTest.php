<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function catalogues_are_closed_and_exact(): void
    {
        self::assertSame(['found', 'empty', 'missing', 'corrupted', 'dependency_unavailable'], array_column(SearchQueryResolutionReadStatus::cases(), 'value'));
        self::assertSame(['applied', 'already_applied', 'divergent_revision', 'version_conflict', 'corrupted', 'dependency_unavailable'], array_column(SearchQueryResolutionWriteResult::cases(), 'value'));
        self::assertSame(['found', 'empty'], array_column(SearchQueryResolutionRevisionDecision::cases(), 'value'));
    }

    #[Test]
    public function mapper_round_trips_without_persisting_the_query(): void
    {
        $mapper = new SearchQueryResolutionOwnerSourceMapper;
        $row = $mapper->toRow(self::state());
        $restored = $mapper->toState($row);

        self::assertArrayNotHasKey('query', $row);
        self::assertSame(hash('sha256', self::query()->canonical()), $row['query_fingerprint']);
        self::assertSame($row, $mapper->toRow($restored));
        self::assertSame('UTC', $restored->effectiveAt->getTimezone()->getName());
    }

    #[Test]
    public function state_rejects_invalid_revision_and_chronology(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SearchQueryResolutionRevisionState(self::query(), 0, SearchQueryResolutionRevisionDecision::Found, new DateTimeImmutable('2026-08-01T08:00:00Z'), new DateTimeImmutable('2026-08-01T08:00:01Z'));
    }

    public static function query(): SearchQuery
    {
        return new SearchQuery('city=dakar&type=apartment');
    }

    public static function state(int $revision = 1, SearchQueryResolutionRevisionDecision $decision = SearchQueryResolutionRevisionDecision::Found, string $time = '08:00:00'): SearchQueryResolutionRevisionState
    {
        return new SearchQueryResolutionRevisionState(self::query(), $revision, $decision, new DateTimeImmutable('2026-08-01T'.$time.'.000000Z'), new DateTimeImmutable('2026-08-01T'.$time.'.100000Z'));
    }
}
