<?php

namespace App\Application\PublicProjectionUpdater;

use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Application\PublicProjectionUpdater\Contract\PublicListingProjectionSource;
use App\Projections\SearchListingProjectionBuilder;
use App\Projections\SeoListingProjectionBuilder;
use App\ReadModels\PublicListingReadModelBuilder;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;

final readonly class PublicListingProjectionUpdater
{
    public function __construct(
        private PublicListingProjectionSource $source,
        private SearchListingProjectionBuilder $searchBuilder,
        private ListingSeoDecisionPolicy $seoDecisionPolicy,
        private SeoListingProjectionBuilder $seoBuilder,
        private PublicListingReadModelBuilder $readModelBuilder,
        private PublicListingProjectionWriter $writer,
    ) {}

    public function update(string $listingId): PublicListingProjectionUpdateResult
    {
        $sources = $this->source->findByListingId($listingId);
        if ($sources === null) {
            return new PublicListingProjectionUpdateResult(PublicListingProjectionUpdateOutcome::SourceUnavailable);
        }

        $search = $this->searchBuilder->build($sources->property, $sources->media, $sources->listing, $sources->transactionKind);
        $decision = $this->seoDecisionPolicy->decide(
            $sources->listingSeo,
            $sources->searchSeo,
            $sources->propertySeo,
            $sources->publicGeographySeo,
            $sources->publicMediaSeo,
            $sources->canonicalHistory,
            $sources->decisionAt,
        );
        $seo = $this->seoBuilder->build($decision);
        $readModel = $this->readModelBuilder->build($search, $seo);

        $watermark = new PublicProjectionWatermark(
            listingVersion: $sources->listing->version(),
            propertyVersion: $sources->property->version(),
            mediaVersion: $sources->media->version(),
            searchVersion: $sources->searchVersion,
            contentSeoVersion: $sources->contentSeoVersion,
            publicGeographyVersion: $sources->publicGeographyVersion,
            publicMediaVersion: $sources->publicMediaVersion,
        );
        $readiness = $watermark->readiness();
        if ($readiness !== PublicProjectionPromotionReadiness::Ready) {
            return new PublicListingProjectionUpdateResult(PublicListingProjectionUpdateOutcome::PromotionNotReady, $readiness);
        }
        if ($readModel === null) {
            return new PublicListingProjectionUpdateResult(PublicListingProjectionUpdateOutcome::ProjectionUnavailable, $readiness);
        }

        $record = PublicListingProjectionRecord::current(
            listingId: $readModel->listingId,
            canonicalPath: $sources->listingSeo->canonicalPath,
            readModel: $readModel,
            watermark: $watermark,
            generationId: $sources->generationId,
        );

        return $this->interpret($this->writer->applyCurrent($record), $readiness);
    }

    private function interpret(PublicProjectionWriteResult $writeResult, PublicProjectionPromotionReadiness $readiness): PublicListingProjectionUpdateResult
    {
        $outcome = match ($writeResult) {
            PublicProjectionWriteResult::Applied => PublicListingProjectionUpdateOutcome::Applied,
            PublicProjectionWriteResult::AlreadyApplied => PublicListingProjectionUpdateOutcome::AlreadyApplied,
            PublicProjectionWriteResult::RejectedObsolete => PublicListingProjectionUpdateOutcome::RejectedObsolete,
            PublicProjectionWriteResult::DivergentWatermark => PublicListingProjectionUpdateOutcome::DivergentWatermark,
            PublicProjectionWriteResult::IncompleteWatermark => PublicListingProjectionUpdateOutcome::IncompleteWatermark,
            PublicProjectionWriteResult::CanonicalCollision => PublicListingProjectionUpdateOutcome::CanonicalCollision,
            PublicProjectionWriteResult::CanonicalReplacementRequired => PublicListingProjectionUpdateOutcome::CanonicalReplacementRequired,
            PublicProjectionWriteResult::HistoricalReservationConflict => PublicListingProjectionUpdateOutcome::HistoricalReservationConflict,
            PublicProjectionWriteResult::GenerationMismatch => PublicListingProjectionUpdateOutcome::GenerationMismatch,
        };

        return new PublicListingProjectionUpdateResult($outcome, $readiness, $writeResult);
    }
}
