<?php

namespace Tests\Unit\SearchDiscovery\SearchOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\SearchOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\SearchDiscovery\SearchOwnerSource\SearchOwnerSourceMapperTest;

final class SearchOwnerSourceRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_policy_is_deterministic_and_diagnostics_are_minimal(
        SearchOwnerReadResult $result,
        SearchOwnerSourceRuntimeAvailability $expected,
    ): void {
        $runtime = $this->runtime(new SearchRuntimeSourceStub($result));

        self::assertSame($expected, $runtime->availability());
        self::assertSame($expected, $runtime->diagnostics()->availability);
        self::assertSame('search-discovery.owner-source', $runtime->diagnostics()->runtimeId);
        self::assertSame('search-owner-source-runtime-v1', $runtime->diagnostics()->version);
        self::assertSame(['runtimeId', 'version', 'availability'], array_keys(get_object_vars($runtime->diagnostics())));
    }

    public function test_technical_exception_is_fail_closed(): void
    {
        self::assertSame(
            SearchOwnerSourceRuntimeAvailability::DependencyUnavailable,
            $this->runtime(new ThrowingSearchRuntimeSourceStub)->availability(),
        );
    }

    /** @return iterable<string, array{SearchOwnerReadResult, SearchOwnerSourceRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        $revision = SearchOwnerSourceMapperTest::state();
        $document = SearchOwnerSourceMapperTest::document();

        yield 'found' => [SearchOwnerReadResult::found($revision), SearchOwnerSourceRuntimeAvailability::Available];
        yield 'missing' => [SearchOwnerReadResult::missing($document), SearchOwnerSourceRuntimeAvailability::Available];
        yield 'corrupted' => [SearchOwnerReadResult::corrupted($document), SearchOwnerSourceRuntimeAvailability::Corrupted];
        yield 'unavailable' => [SearchOwnerReadResult::dependencyUnavailable($document), SearchOwnerSourceRuntimeAvailability::DependencyUnavailable];
    }

    private function runtime(SearchOwnerSource $source): DeterministicSearchOwnerSourceRuntimeV1
    {
        return new DeterministicSearchOwnerSourceRuntimeV1(new DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy($source));
    }
}

final readonly class SearchRuntimeSourceStub implements SearchOwnerSource
{
    public function __construct(private SearchOwnerReadResult $result) {}

    public function append(SearchOwnerRevisionState $revision): SearchOwnerWriteResult
    {
        return SearchOwnerWriteResult::DependencyUnavailable;
    }

    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerReadResult
    {
        return $this->result;
    }

    public function history(SearchDocumentId $documentId): array
    {
        return [];
    }
}

final readonly class ThrowingSearchRuntimeSourceStub implements SearchOwnerSource
{
    public function append(SearchOwnerRevisionState $revision): SearchOwnerWriteResult
    {
        throw new RuntimeException('secret');
    }

    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerReadResult
    {
        throw new RuntimeException('secret');
    }

    public function history(SearchDocumentId $documentId): array
    {
        throw new RuntimeException('secret');
    }
}
