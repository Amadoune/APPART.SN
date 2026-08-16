<?php

namespace Tests\Unit\PublicSearchDecisionMaterialization;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchDecisionMaterializerV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchDecisionIdentityV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationSourceResult;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationSources;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationStatus;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFacetPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchVisibilityPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicSearchDecisionMaterializerTest extends TestCase
{
    private const string LISTING = '979cd5aa-ced1-48a1-8adf-8b29c843a0c2';

    public function test_policy_and_identity_match_the_certified_vector(): void
    {
        $policy = (new DeterministicPublicSearchRankingPolicyV1)->decide();
        self::assertSame('public-search-ranking-policy-v1', $policy->policyId);
        self::assertSame(0, $policy->rank->value);
        self::assertSame([], $policy->facets);
        self::assertSame('20d5ab5a-45ac-5a5e-9f15-d1357e9105a9', (new PublicSearchDecisionIdentityV1)->issue($this->id(), $policy->policyId)->value);
    }

    public function test_applied_replay_dominant_obsolete_and_divergent_are_deterministic(): void
    {
        $source = new MutableSourceReader($this->sources(1));
        $store = new InMemoryDecisionStore;
        $materializer = $this->materializer($source, $store);

        $applied = $materializer->materialize($this->id());
        self::assertSame(PublicSearchMaterializationStatus::Applied, $applied->status);
        self::assertSame(1, $applied->decision?->version);
        self::assertSame(0, $applied->decision?->projection->rank->value);
        self::assertSame([], $applied->decision?->projection->facets);

        self::assertSame(PublicSearchMaterializationStatus::AlreadyApplied, $materializer->materialize($this->id())->status);
        self::assertSame(1, $store->decision?->version);

        $source->result = $this->sources(2);
        $newer = $materializer->materialize($this->id());
        self::assertSame(PublicSearchMaterializationStatus::Applied, $newer->status);
        self::assertSame(2, $newer->decision?->version);
        self::assertSame($applied->decision?->decisionId->value, $newer->decision?->decisionId->value);

        $source->result = $this->sources(1);
        self::assertSame(PublicSearchMaterializationStatus::RejectedObsolete, $materializer->materialize($this->id())->status);
        self::assertSame(2, $store->decision?->version);

        $source->result = $this->sources(2, 'dddddddd-dddd-4ddd-8ddd-dddddddddddd');
        self::assertSame(PublicSearchMaterializationStatus::Divergent, $materializer->materialize($this->id())->status);
        self::assertSame(2, $store->decision?->version);
    }

    #[DataProvider('failureProvider')]
    public function test_source_failures_are_closed(PublicSearchMaterializationSourceResult $source, PublicSearchMaterializationStatus $expected): void
    {
        self::assertSame($expected, $this->materializer(new MutableSourceReader($source), new InMemoryDecisionStore)->materialize($this->id())->status);
    }

    public static function failureProvider(): iterable
    {
        yield 'missing' => [PublicSearchMaterializationSourceResult::missing(), PublicSearchMaterializationStatus::SourceMissing];
        yield 'corrupted' => [PublicSearchMaterializationSourceResult::corrupted(), PublicSearchMaterializationStatus::SourceCorrupted];
        yield 'unavailable' => [PublicSearchMaterializationSourceResult::dependencyUnavailable(), PublicSearchMaterializationStatus::DependencyUnavailable];
    }

    private function materializer(PublicSearchMaterializationSourceReaderV1 $source, InMemoryDecisionStore $store): DeterministicPublicSearchDecisionMaterializerV1
    {
        return new DeterministicPublicSearchDecisionMaterializerV1(
            $source,
            new DeterministicPublicSearchRankingPolicyV1,
            new SearchProjectionPolicy(new SearchVisibilityPolicy, new SearchFacetPolicy),
            new PublicSearchDecisionIdentityV1,
            $store,
            $store,
        );
    }

    private function sources(int $mediaVersion, string $mediaFact = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc'): PublicSearchMaterializationSourceResult
    {
        return PublicSearchMaterializationSourceResult::found(new PublicSearchMaterializationSources(
            ListingSearchState::Published,
            PropertySearchState::Available,
            MediaSearchState::Ready,
            SourceRevision::create(SourceKind::Listing, 4, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', new DateTimeImmutable('2026-08-15T07:22:24Z')),
            SourceRevision::create(SourceKind::Property, 1, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', new DateTimeImmutable('2026-08-15T07:16:27Z')),
            SourceRevision::create(SourceKind::Media, $mediaVersion, $mediaFact, new DateTimeImmutable('2026-08-15T07:01:01Z')),
        ));
    }

    private function id(): ListingId
    {
        return ListingId::fromString(self::LISTING);
    }
}

final class MutableSourceReader implements PublicSearchMaterializationSourceReaderV1
{
    public function __construct(public PublicSearchMaterializationSourceResult $result) {}

    public function read(ListingId $listingId): PublicSearchMaterializationSourceResult
    {
        return $this->result;
    }
}

final class InMemoryDecisionStore implements SearchDecisionReader, SearchDecisionWriter
{
    public ?SearchDecision $decision = null;

    public function readByListing(ListingId $listingId): SearchDecisionReadResult
    {
        return $this->decision === null ? SearchDecisionReadResult::missing($listingId) : SearchDecisionReadResult::found($listingId, $this->decision);
    }

    public function store(SearchDecision $decision): SearchDecisionWriteResult
    {
        if ($this->decision !== null) {
            if ($decision->version < $this->decision->version) {
                return SearchDecisionWriteResult::RejectedObsolete;
            }
            if ($decision->version === $this->decision->version) {
                return $decision->projection->revisions->for(SourceKind::Media)->sameFact($this->decision->projection->revisions->for(SourceKind::Media))
                    ? SearchDecisionWriteResult::AlreadyApplied : SearchDecisionWriteResult::Divergent;
            }
        }
        $this->decision = $decision;

        return SearchDecisionWriteResult::Applied;
    }
}
