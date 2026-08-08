<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerReader;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerReader;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SearchQueryResolutionOwnerReaderTest extends TestCase
{
    #[DataProvider('statusMappings')]
    public function test_policy_is_exhaustive_and_fail_closed(
        SearchQueryResolutionReadStatus $source,
        SearchQueryResolutionOwnerStatus $expected,
    ): void {
        self::assertSame($expected, (new SearchQueryResolutionOwnerPolicy)->reduce($source));
    }

    public static function statusMappings(): iterable
    {
        yield [SearchQueryResolutionReadStatus::Found, SearchQueryResolutionOwnerStatus::Found];
        yield [SearchQueryResolutionReadStatus::Empty, SearchQueryResolutionOwnerStatus::Empty];
        yield [SearchQueryResolutionReadStatus::Missing, SearchQueryResolutionOwnerStatus::Corrupted];
        yield [SearchQueryResolutionReadStatus::Corrupted, SearchQueryResolutionOwnerStatus::Corrupted];
        yield [SearchQueryResolutionReadStatus::DependencyUnavailable, SearchQueryResolutionOwnerStatus::DependencyUnavailable];
    }

    public function test_reader_passes_the_real_query_and_observation_to_the_owner_source(): void
    {
        $query = new SearchQuery('balcony dakar');
        $observedAt = new SearchQueryResolutionObservedAt(new DateTimeImmutable('2026-08-02T12:00:00+00:00'));
        $source = new class($query, $observedAt) implements SearchQueryResolutionOwnerSource
        {
            public function __construct(private SearchQuery $expectedQuery, private SearchQueryResolutionObservedAt $expectedObservedAt) {}

            public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult
            {
                throw new RuntimeException('unused');
            }

            public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult
            {
                TestCase::assertSame($this->expectedQuery, $query);
                TestCase::assertSame($this->expectedObservedAt, $observedAt);

                return SearchQueryResolutionReadResult::missing($query);
            }

            public function history(SearchQuery $query): array
            {
                return [];
            }
        };

        $result = (new SearchQueryResolutionOwnerReader($source, new SearchQueryResolutionOwnerPolicy))->read($query, $observedAt);

        self::assertSame(SearchQueryResolutionOwnerStatus::Corrupted, $result->status);
    }

    public function test_technical_exception_is_dependency_unavailable(): void
    {
        $source = new class implements SearchQueryResolutionOwnerSource
        {
            public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult
            {
                throw new RuntimeException('unused');
            }

            public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult
            {
                throw new RuntimeException('offline');
            }

            public function history(SearchQuery $query): array
            {
                return [];
            }
        };
        $reader = new SearchQueryResolutionOwnerReader($source, new SearchQueryResolutionOwnerPolicy);

        $result = $reader->read(new SearchQuery('query'), new SearchQueryResolutionObservedAt(new DateTimeImmutable));

        self::assertSame(SearchQueryResolutionOwnerStatus::DependencyUnavailable, $result->status);
    }
}
