<?php

namespace Tests\Unit\SearchDiscovery\SearchOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\SearchDiscovery\SearchOwnerSource\SearchOwnerSourceMapperTest;

final class SearchOwnerSourceRuntimeReadTest extends TestCase
{
    #[DataProvider('reductionCases')]
    public function test_policy_reduces_every_source_status_mechanically(SearchOwnerReadResult $source, SearchOwnerSourceRuntimeReadStatus $expected): void
    {
        self::assertSame($expected, (new DeterministicSearchOwnerSourceRuntimeReadPolicy)->reduce($source)->status);
    }

    public function test_runtime_read_and_diagnostics_are_closed(): void
    {
        $revision = SearchOwnerSourceMapperTest::state();
        $runtime = $this->runtime(new RuntimeReadSourceStub(SearchOwnerReadResult::found($revision)));

        self::assertSame(SearchOwnerSourceRuntimeReadStatus::Allowed, $runtime->read($revision->documentId, self::observedAt())->status);
        self::assertSame(SearchOwnerSourceRuntimeReadAvailability::Available, $runtime->diagnostics()->availability);
        self::assertSame('search-discovery.owner-source-runtime-read', $runtime->diagnostics()->runtimeReadId);
        self::assertSame('search-owner-source-runtime-read-v1', $runtime->diagnostics()->version);
        self::assertSame(['runtimeReadId', 'version', 'availability'], array_keys(get_object_vars($runtime->diagnostics())));
    }

    public function test_exception_is_dependency_unavailable(): void
    {
        $runtime = $this->runtime(new ThrowingRuntimeReadSourceStub);
        self::assertSame(SearchOwnerSourceRuntimeReadStatus::DependencyUnavailable, $runtime->read(SearchOwnerSourceMapperTest::document(), self::observedAt())->status);
        self::assertSame(SearchOwnerSourceRuntimeReadAvailability::DependencyUnavailable, $runtime->diagnostics()->availability);
    }

    /** @return iterable<string, array{SearchOwnerReadResult, SearchOwnerSourceRuntimeReadStatus}> */
    public static function reductionCases(): iterable
    {
        $revision = SearchOwnerSourceMapperTest::state();
        $document = $revision->documentId;
        yield 'found' => [SearchOwnerReadResult::found($revision), SearchOwnerSourceRuntimeReadStatus::Allowed];
        yield 'missing' => [SearchOwnerReadResult::missing($document), SearchOwnerSourceRuntimeReadStatus::Empty];
        yield 'corrupted' => [SearchOwnerReadResult::corrupted($document), SearchOwnerSourceRuntimeReadStatus::Corrupted];
        yield 'unavailable' => [SearchOwnerReadResult::dependencyUnavailable($document), SearchOwnerSourceRuntimeReadStatus::DependencyUnavailable];
    }

    private function runtime(SearchOwnerSource $source): DeterministicSearchOwnerSourceRuntimeReadV1
    {
        return new DeterministicSearchOwnerSourceRuntimeReadV1($source, new DeterministicSearchOwnerSourceRuntimeReadPolicy);
    }

    private static function observedAt(): SearchObservedAt
    {
        return new SearchObservedAt(new DateTimeImmutable('2026-08-02T00:00:00Z'));
    }
}

final readonly class RuntimeReadSourceStub implements SearchOwnerSource
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

final readonly class ThrowingRuntimeReadSourceStub implements SearchOwnerSource
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
