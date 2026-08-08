<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeDiagnostics;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\SearchQueryResolutionOwnerSourceRuntimeReadAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\SearchQueryResolutionOwnerSourceRuntimeReadStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SearchQueryResolutionOwnerSourceRuntimeReadTest extends TestCase
{
    #[DataProvider('reductionCases')]
    public function test_reduction_is_exhaustive_and_diagnostics_are_minimal(
        SearchQueryResolutionOwnerSourceRuntimeAvailability $source,
        SearchQueryResolutionOwnerSourceRuntimeReadStatus $expectedStatus,
        SearchQueryResolutionOwnerSourceRuntimeReadAvailability $expectedAvailability,
    ): void {
        $runtimeRead = $this->runtimeRead(new QueryResolutionRuntimeStub($source));
        self::assertSame($expectedStatus, $runtimeRead->read()->status);
        self::assertSame($expectedAvailability, $runtimeRead->diagnostics()->availability);
        self::assertSame('search-query-resolution-runtime-read', $runtimeRead->diagnostics()->runtimeId);
        self::assertSame('search-query-resolution-runtime-read-v1', $runtimeRead->diagnostics()->version);
        self::assertSame(['runtimeId', 'version', 'availability'], array_keys(get_object_vars($runtimeRead->diagnostics())));
    }

    public function test_exception_is_fail_closed_without_fallback(): void
    {
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeReadStatus::DependencyUnavailable, $this->runtimeRead(new ThrowingQueryResolutionRuntimeStub)->read()->status);
    }

    /** @return iterable<string, array{SearchQueryResolutionOwnerSourceRuntimeAvailability, SearchQueryResolutionOwnerSourceRuntimeReadStatus, SearchQueryResolutionOwnerSourceRuntimeReadAvailability}> */
    public static function reductionCases(): iterable
    {
        yield 'available' => [SearchQueryResolutionOwnerSourceRuntimeAvailability::Available, SearchQueryResolutionOwnerSourceRuntimeReadStatus::Found, SearchQueryResolutionOwnerSourceRuntimeReadAvailability::Available];
        yield 'corrupted' => [SearchQueryResolutionOwnerSourceRuntimeAvailability::Corrupted, SearchQueryResolutionOwnerSourceRuntimeReadStatus::Corrupted, SearchQueryResolutionOwnerSourceRuntimeReadAvailability::Corrupted];
        yield 'unavailable' => [SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable, SearchQueryResolutionOwnerSourceRuntimeReadStatus::DependencyUnavailable, SearchQueryResolutionOwnerSourceRuntimeReadAvailability::DependencyUnavailable];
    }

    private function runtimeRead(SearchQueryResolutionOwnerSourceRuntimeV1 $runtime): DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1
    {
        return new DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1($runtime, new DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy);
    }
}

final readonly class QueryResolutionRuntimeStub implements SearchQueryResolutionOwnerSourceRuntimeV1
{
    public function __construct(private SearchQueryResolutionOwnerSourceRuntimeAvailability $availability) {}

    public function availability(): SearchQueryResolutionOwnerSourceRuntimeAvailability
    {
        return $this->availability;
    }

    public function diagnostics(): SearchQueryResolutionOwnerSourceRuntimeDiagnostics
    {
        return new SearchQueryResolutionOwnerSourceRuntimeDiagnostics('stub', 'stub-v1', $this->availability);
    }
}

final readonly class ThrowingQueryResolutionRuntimeStub implements SearchQueryResolutionOwnerSourceRuntimeV1
{
    public function availability(): SearchQueryResolutionOwnerSourceRuntimeAvailability
    {
        throw new RuntimeException('secret');
    }

    public function diagnostics(): SearchQueryResolutionOwnerSourceRuntimeDiagnostics
    {
        throw new RuntimeException('secret');
    }
}
