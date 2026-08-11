<?php

namespace App\Infrastructure\ProjectionRuntimeSource;

use App\Application\ActiveGenerationReader\ActiveGenerationReadStatus;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Application\DecisionTimeSource\DecisionTimeReadStatus;
use App\Application\ProjectionRuntimeSource\Contract\CandidatePublicListingProjectionSource;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyResult;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicGeographySource\PublicGeographyReadStatus;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\PublicMediaReadStatus;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionUpdater\PublicListingProjectionSources;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadStatus;
use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as SeoListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResolution;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use Throwable;

final readonly class CertifiedPublicListingProjectionSource implements CandidatePublicListingProjectionSource
{
    public function __construct(
        private ListingRegistry $listings,
        private PropertyRegistry $properties,
        private MediaCollectionOwnershipLookup $mediaOwnership,
        private MediaCollectionRegistry $mediaCollections,
        private SearchDecisionReader $searchDecisions,
        private ContentSeoSourceSnapshotReader $contentSeoSnapshots,
        private PublicGeographyDecisionReader $publicGeography,
        private PublicMediaDecisionReader $publicMedia,
        private ActiveGenerationReader $activeGeneration,
        private DecisionTimeReader $decisionTimes,
        private ?AuthoringPublicFactHandoffV1 $publicFacts = null,
    ) {}

    public function findByListingId(string $listingId): ?PublicListingProjectionSources
    {
        return $this->inspect($listingId)->sources;
    }

    public function inspect(string $listingId): ProjectionSourceAssemblyResult
    {
        return $this->assemble($listingId);
    }

    public function inspectForGeneration(string $listingId, PublicProjectionGenerationId $generationId): ProjectionSourceAssemblyResult
    {
        return $this->assemble($listingId, $generationId);
    }

    private function assemble(string $listingId, ?PublicProjectionGenerationId $candidateGeneration = null): ProjectionSourceAssemblyResult
    {
        try {
            $listingIdentity = ListingId::fromString($listingId);
            $searchIdentity = SearchListingId::fromString($listingId);
            $seoIdentity = SeoListingId::fromString($listingId);
        } catch (Throwable) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::InvalidListingIdentity);
        }

        $listing = $this->listings->find($listingIdentity);
        if ($listing === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::ListingMissing);
        }
        $property = $this->properties->find(PropertyId::fromString($listing->propertyId()->value));
        if ($property === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::PropertyMissing);
        }

        $ownership = $this->mediaOwnership->resolve(MediaPropertyId::fromString($property->id()->value));
        if ($ownership->propertyId->value !== $property->id()->value) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::SourceIdentityDivergent);
        }
        if ($ownership->resolution === MediaOwnershipResolution::Missing) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::MediaOwnershipMissing);
        }
        if ($ownership->resolution === MediaOwnershipResolution::Ambiguous || $ownership->collectionId === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::MediaOwnershipAmbiguous);
        }
        $media = $this->mediaCollections->find($ownership->collectionId);
        if ($media === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::MediaCollectionMissing);
        }
        if ($media->propertyId()->value !== $property->id()->value) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::SourceIdentityDivergent);
        }

        $search = $this->searchDecisions->readByListing($searchIdentity);
        if ($search->listingId->value !== $listingId) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::SourceIdentityDivergent);
        }
        if ($search->status !== SearchDecisionReadStatus::Found || $search->decision === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, $search->status === SearchDecisionReadStatus::Missing ? ProjectionSourceAssemblyStatus::SearchMissing : ProjectionSourceAssemblyStatus::SearchCorrupted);
        }
        $snapshot = $this->contentSeoSnapshots->readByListing($seoIdentity);
        if ($snapshot->listingId->value !== $listingId) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::SourceIdentityDivergent);
        }
        if ($snapshot->status !== ContentSeoSnapshotReadStatus::Found || $snapshot->snapshot === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, $snapshot->status === ContentSeoSnapshotReadStatus::Missing ? ProjectionSourceAssemblyStatus::ContentSeoMissing : ProjectionSourceAssemblyStatus::ContentSeoCorrupted);
        }

        $generationId = $candidateGeneration;
        if ($generationId === null) {
            $generation = $this->activeGeneration->read();
            if ($generation->status !== ActiveGenerationReadStatus::Found || $generation->generation === null) {
                return ProjectionSourceAssemblyResult::blocked($listingId, $generation->status === ActiveGenerationReadStatus::Missing ? ProjectionSourceAssemblyStatus::ActiveGenerationMissing : ProjectionSourceAssemblyStatus::ActiveGenerationCorrupted);
            }
            $generationId = $generation->generation->id;
        }
        $decisionTime = $this->decisionTimes->readByListing($listingId);
        if ($decisionTime->listingId !== $listingId) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::SourceIdentityDivergent);
        }
        if ($decisionTime->status !== DecisionTimeReadStatus::Found || $decisionTime->decisionAt === null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, $decisionTime->status === DecisionTimeReadStatus::Missing ? ProjectionSourceAssemblyStatus::DecisionTimeMissing : ProjectionSourceAssemblyStatus::DecisionTimeCorrupted);
        }
        if ($decisionTime->decisionAt->format('Y-m-d\TH:i:s.uP') !== $snapshot->snapshot->decisionAt->format('Y-m-d\TH:i:s.uP')) {
            return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::DecisionTimeDivergent);
        }

        [$geographySeo, $geographyVersion, $geographyFailure] = $this->geography($seoIdentity, $property->address()?->placeId->value);
        if ($geographyFailure !== null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, $geographyFailure);
        }
        [$mediaSeo, $mediaVersion, $mediaFailure] = $this->media($seoIdentity, $ownership->collectionId->value);
        if ($mediaFailure !== null) {
            return ProjectionSourceAssemblyResult::blocked($listingId, $mediaFailure);
        }

        $sources = new PublicListingProjectionSources(
            $property,
            $media,
            $listing,
            $snapshot->snapshot->listing,
            $snapshot->snapshot->search,
            $snapshot->snapshot->property,
            $geographySeo,
            $mediaSeo,
            $snapshot->snapshot->canonicalHistory,
            $decisionTime->decisionAt,
            $search->decision->version,
            $snapshot->snapshot->version,
            $geographyVersion,
            $mediaVersion,
            $generationId,
            $this->publicFacts?->published($listingId)?->transactionKind->value,
        );

        return ProjectionSourceAssemblyResult::found($listingId, $sources);
    }

    /** @return array{?PublicGeographySeoSource, ?int, ?ProjectionSourceAssemblyStatus} */
    private function geography(SeoListingId $listingId, ?string $placeId): array
    {
        if ($placeId === null) {
            return [null, null, null];
        }
        $result = $this->publicGeography->read($placeId);
        if ($result->placeId !== $placeId) {
            return [null, null, ProjectionSourceAssemblyStatus::SourceIdentityDivergent];
        }
        if ($result->status === PublicGeographyReadStatus::Missing) {
            return [null, null, null];
        }
        if ($result->status === PublicGeographyReadStatus::Corrupted || $result->decision === null) {
            return [null, null, ProjectionSourceAssemblyStatus::PublicGeographyCorrupted];
        }
        try {
            $breadcrumb = array_map(
                static fn ($item): BreadcrumbItem => new BreadcrumbItem($item->label, CanonicalUrl::fromString($item->url)),
                $result->decision->breadcrumb,
            );
            $source = new PublicGeographySeoSource($listingId, $result->decision->locality, $breadcrumb);
        } catch (Throwable) {
            return [null, null, ProjectionSourceAssemblyStatus::PublicGeographyCorrupted];
        }

        return [$source, $result->decision->revision->watermarkVersion(), null];
    }

    /** @return array{?PublicMediaSeoSource, ?int, ?ProjectionSourceAssemblyStatus} */
    private function media(SeoListingId $listingId, string $mediaCollectionId): array
    {
        $result = $this->publicMedia->read($mediaCollectionId);
        if ($result->mediaCollectionId !== $mediaCollectionId) {
            return [null, null, ProjectionSourceAssemblyStatus::SourceIdentityDivergent];
        }
        if ($result->status === PublicMediaReadStatus::Missing) {
            return [null, null, null];
        }
        if ($result->status === PublicMediaReadStatus::Corrupted || $result->decision === null) {
            return [null, null, ProjectionSourceAssemblyStatus::PublicMediaCorrupted];
        }
        if ($result->decision->cover === null) {
            return [null, null, null];
        }

        try {
            $source = new PublicMediaSeoSource($listingId, PublicMediaUrl::fromString($result->decision->cover->url));
        } catch (Throwable) {
            return [null, null, ProjectionSourceAssemblyStatus::PublicMediaCorrupted];
        }

        return [$source, $result->decision->revision->watermarkVersion(), null];
    }
}
