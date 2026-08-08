<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeAvailability;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSource\SearchQueryResolutionOwnerSourceMapperTest;

final class SearchQueryResolutionOwnerSourceRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_policy_is_deterministic_and_diagnostics_are_minimal(
        SearchQueryResolutionReadResult $result,
        SearchQueryResolutionOwnerSourceRuntimeAvailability $expected,
    ): void {
        $runtime = $this->runtime(new QueryResolutionRuntimeSourceStub($result));

        self::assertSame($expected, $runtime->availability());
        self::assertSame($expected, $runtime->diagnostics()->availability);
        self::assertSame('search-query-resolution-owner-source-runtime', $runtime->diagnostics()->runtimeId);
        self::assertSame('search-query-resolution-owner-source-runtime-v1', $runtime->diagnostics()->version);
        self::assertSame(['runtimeId', 'version', 'availability'], array_keys(get_object_vars($runtime->diagnostics())));
    }

    public function test_technical_exception_is_fail_closed(): void
    {
        self::assertSame(
            SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable,
            $this->runtime(new ThrowingQueryResolutionRuntimeSourceStub)->availability(),
        );
    }

    /** @return iterable<string, array{SearchQueryResolutionReadResult, SearchQueryResolutionOwnerSourceRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        $revision = SearchQueryResolutionOwnerSourceMapperTest::state();
        $query = SearchQueryResolutionOwnerSourceMapperTest::query();

        yield 'found' => [SearchQueryResolutionReadResult::found($revision), SearchQueryResolutionOwnerSourceRuntimeAvailability::Available];
        yield 'empty' => [SearchQueryResolutionReadResult::found(SearchQueryResolutionOwnerSourceMapperTest::state(1, SearchQueryResolutionRevisionDecision::Empty)), SearchQueryResolutionOwnerSourceRuntimeAvailability::Available];
        yield 'missing' => [SearchQueryResolutionReadResult::missing($query), SearchQueryResolutionOwnerSourceRuntimeAvailability::Available];
        yield 'corrupted' => [SearchQueryResolutionReadResult::corrupted($query), SearchQueryResolutionOwnerSourceRuntimeAvailability::Corrupted];
        yield 'unavailable' => [SearchQueryResolutionReadResult::dependencyUnavailable($query), SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable];
    }

    private function runtime(SearchQueryResolutionOwnerSource $source): DeterministicSearchQueryResolutionOwnerSourceRuntimeV1
    {
        return new DeterministicSearchQueryResolutionOwnerSourceRuntimeV1(new DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy($source));
    }
}

final readonly class QueryResolutionRuntimeSourceStub implements SearchQueryResolutionOwnerSource
{
    public function __construct(private SearchQueryResolutionReadResult $result) {}

    public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult
    {
        return SearchQueryResolutionWriteResult::DependencyUnavailable;
    }

    public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult
    {
        return $this->result;
    }

    public function history(SearchQuery $query): array
    {
        return [];
    }
}

final readonly class ThrowingQueryResolutionRuntimeSourceStub implements SearchQueryResolutionOwnerSource
{
    public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult
    {
        throw new RuntimeException('secret');
    }

    public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult
    {
        throw new RuntimeException('secret');
    }

    public function history(SearchQuery $query): array
    {
        throw new RuntimeException('secret');
    }
}
