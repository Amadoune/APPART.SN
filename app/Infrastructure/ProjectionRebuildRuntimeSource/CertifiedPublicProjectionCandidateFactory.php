<?php

namespace App\Infrastructure\ProjectionRebuildRuntimeSource;

use App\Application\ProjectionRebuildRuntimeSource\CandidateBuildResult;
use App\Application\ProjectionRebuildRuntimeSource\CandidateBuildStatus;
use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\ProjectionRuntimeSource\Contract\CandidatePublicListingProjectionSource;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Projections\SearchListingProjectionBuilder;
use App\Projections\SeoListingProjectionBuilder;
use App\ReadModels\PublicListingReadModelBuilder;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;
use Throwable;

final readonly class CertifiedPublicProjectionCandidateFactory implements InspectablePublicProjectionCandidateFactory
{
    public function __construct(
        private CandidatePublicListingProjectionSource $source,
        private SearchListingProjectionBuilder $searchBuilder,
        private ListingSeoDecisionPolicy $seoDecisionPolicy,
        private SeoListingProjectionBuilder $seoBuilder,
        private PublicListingReadModelBuilder $readModelBuilder,
    ) {}

    public function rebuild(string $listingId, PublicProjectionGenerationId $generationId): ?PublicListingProjectionRecord
    {
        return $this->inspect($listingId, $generationId)->record;
    }

    public function inspect(string $listingId, PublicProjectionGenerationId $generationId): CandidateBuildResult
    {
        $assembled = $this->source->inspectForGeneration($listingId, $generationId);
        if ($assembled->sources === null || $assembled->watermark === null) {
            return CandidateBuildResult::blocked($listingId, CandidateBuildStatus::SourceBlocked, $assembled->status, $assembled->readiness);
        }
        if ($assembled->readiness !== PublicProjectionPromotionReadiness::Ready) {
            return CandidateBuildResult::blocked($listingId, CandidateBuildStatus::PromotionNotReady, $assembled->status, $assembled->readiness);
        }

        try {
            $search = $this->searchBuilder->build($assembled->sources->property, $assembled->sources->media, $assembled->sources->listing, $assembled->sources->transactionKind);
            $decision = $this->seoDecisionPolicy->decide(
                $assembled->sources->listingSeo,
                $assembled->sources->searchSeo,
                $assembled->sources->propertySeo,
                $assembled->sources->publicGeographySeo,
                $assembled->sources->publicMediaSeo,
                $assembled->sources->canonicalHistory,
                $assembled->sources->decisionAt,
            );
            $seo = $this->seoBuilder->build($decision);
            $readModel = $this->readModelBuilder->build($search, $seo);
        } catch (Throwable) {
            return CandidateBuildResult::blocked($listingId, CandidateBuildStatus::CertifiedTransformationRejected, $assembled->status, $assembled->readiness);
        }
        if ($readModel === null) {
            return CandidateBuildResult::blocked($listingId, CandidateBuildStatus::ProjectionUnavailable, $assembled->status, $assembled->readiness);
        }

        return CandidateBuildResult::built($listingId, PublicListingProjectionRecord::current(
            $readModel->listingId,
            $assembled->sources->listingSeo->canonicalPath,
            $readModel,
            $assembled->watermark,
            $generationId,
        ));
    }
}
