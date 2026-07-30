<?php

namespace Tests\Unit\ProjectionRebuildRuntimeSource;

use App\Application\ProjectionRebuildRuntimeSource\CandidateBuildStatus;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PublicGeographySource\PublicGeographyReadResult;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuilder;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildPage;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Infrastructure\ProjectionRebuildRuntimeSource\CertifiedPublicProjectionCandidateFactory;
use App\Projections\SearchListingProjectionBuilder;
use App\Projections\SeoListingProjectionBuilder;
use App\ReadModels\PublicListingReadModelBuilder;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use PHPUnit\Framework\TestCase;
use Tests\Unit\ProjectionRuntimeSource\CertifiedPublicListingProjectionSourceTest;

final class CertifiedPublicProjectionCandidateFactoryTest extends TestCase
{
    public const string LISTING = '99400000-0000-4000-8000-000000000001';

    private const string GENERATION = '9c000000-0000-4000-8000-000000000001';

    public function test_valid_certified_source_builds_candidate_for_requested_generation(): void
    {
        $result = $this->factory(CertifiedPublicListingProjectionSourceTest::scenario()->source())->inspect(self::LISTING, $this->generation());

        self::assertSame(CandidateBuildStatus::Built, $result->status);
        self::assertNotNull($result->record);
        self::assertSame(self::LISTING, $result->record->listingId);
        self::assertTrue($this->generation()->equals($result->record->generationId));
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $result->record->watermark->readiness());
    }

    public function test_non_promotable_or_corrupted_source_never_builds_candidate(): void
    {
        $scenario = CertifiedPublicListingProjectionSourceTest::scenario();
        $scenario->geography = PublicGeographyReadResult::missing('place:dakar:plateau');
        $notReady = $this->factory($scenario->source())->inspect(self::LISTING, $this->generation());
        self::assertSame(CandidateBuildStatus::PromotionNotReady, $notReady->status);
        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicGeographyVersion, $notReady->readiness);
        self::assertNull($notReady->record);

        $scenario = CertifiedPublicListingProjectionSourceTest::scenario();
        $scenario->search = SearchDecisionReadResult::corrupted(SearchListingId::fromString(self::LISTING));
        $blocked = $this->factory($scenario->source())->inspect(self::LISTING, $this->generation());
        self::assertSame(CandidateBuildStatus::SourceBlocked, $blocked->status);
        self::assertSame(ProjectionSourceAssemblyStatus::SearchCorrupted, $blocked->sourceStatus);
        self::assertNull($blocked->record);
    }

    public function test_rebuilder_reprocessing_same_page_converges_to_already_applied(): void
    {
        $factory = $this->factory(CertifiedPublicListingProjectionSourceTest::scenario()->source());
        $enumerator = new readonly class implements PublicProjectionRebuildEnumerator
        {
            public function page(PublicProjectionRebuildScope $scope, ?string $checkpoint, int $limit): PublicProjectionRebuildPage
            {
                return new PublicProjectionRebuildPage([CertifiedPublicProjectionCandidateFactoryTest::LISTING], null);
            }
        };
        $writer = new ConvergentCandidateWriter;
        $rebuilder = new PublicProjectionRebuilder($enumerator, $factory, $writer, 10);

        $first = $rebuilder->runOnce($this->generation(), PublicProjectionRebuildScope::full());
        $replayed = $rebuilder->runOnce($this->generation(), PublicProjectionRebuildScope::full());
        self::assertSame([1, 0, 0], [$first->applied, $first->alreadyApplied, $first->missing]);
        self::assertSame([0, 1, 0], [$replayed->applied, $replayed->alreadyApplied, $replayed->missing]);
        self::assertSame([], $replayed->rejectedListingIds);
    }

    private function factory(InspectablePublicListingProjectionSource $source): CertifiedPublicProjectionCandidateFactory
    {
        return new CertifiedPublicProjectionCandidateFactory(
            $source,
            new SearchListingProjectionBuilder,
            new ListingSeoDecisionPolicy(new CanonicalPolicy, new CanonicalHistoryPolicy),
            new SeoListingProjectionBuilder,
            new PublicListingReadModelBuilder,
        );
    }

    private function generation(): PublicProjectionGenerationId
    {
        return PublicProjectionGenerationId::fromString(self::GENERATION);
    }
}

final class ConvergentCandidateWriter implements PublicListingProjectionWriter
{
    private ?PublicListingProjectionRecord $stored = null;

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        if ($this->stored !== null) {
            return PublicProjectionWriteResult::AlreadyApplied;
        }
        $this->stored = $record;

        return PublicProjectionWriteResult::Applied;
    }
}
